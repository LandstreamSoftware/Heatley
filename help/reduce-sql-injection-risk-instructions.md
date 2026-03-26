# Instructions

## The problem with my code

I've had feed back about my website files:
"There’s a few pages that aren’t using prepared statements. If we can mitigate SQL injection possibility that would be ideal. For example, we have a $_SERVER query_string that’s parsing to a variable that’s being passed directly into a SQL query." Many of my SQL queries are in the form of: $sql = "SELECT * from [my_table] WHERE id = [$query_parameter].
Do I need to change these to prepared statements like: $stmt = $con->prepare("SELECT * from [my_table] WHERE id = ?") etc

## Answer

Yes — for any SQL that includes user-controlled input, you should change it to a parameterized query / prepared statement. That is the standard mitigation for SQL injection, because the SQL and the data are sent separately, so the input is treated as a value rather than executable SQL.

So this pattern is not safe if $query_parameter can come from $_GET, $_POST, $_SERVER['QUERY_STRING'], route params, cookies, or anything derived from the request:

```
$sql = "SELECT * FROM my_table WHERE id = $query_parameter";
```

This is the right direction instead:

```
$accountid = $_SESSION['account_id'] ?? null;
if (!is_int($accountid) && !ctype_digit($accountid)) {
    exit('Invalid account ID');
}
$accountid = (int)$accountid;

$stmt = $con->prepare("SELECT * FROM accesscontrol WHERE accountID = ?");
$stmt->bind_param("i", $accountid);
$stmt->execute();
$resultAccess = $stmt->get_result();
```

## Fix ORDER BY with variables

This is bad:

```
$order = $_GET['sort'];
$sql = "SELECT * FROM users ORDER BY $order";
```

That should become an allowlist:

```
$allowedSort = ['name', 'created_at', 'email'];
$sort = $_GET['sort'] ?? 'name';
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'name';
}
$sql = "SELECT * FROM users ORDER BY $sort";
```

## Practical triage order

Review matches in this order:

* Anything using $_GET, $_POST, $_REQUEST, $_SERVER
* Anything building SQL with $variable inside the string
* Anything using string concatenation to build SQL
* Anything with dynamic ORDER BY, table names, or column names
* Then $_SESSION-derived queries

# Use Visual Studio Code

## 1. Find SQL queries with variables

Finds SQL strings that include PHP variables — your main risk

```
regex
SELECT .*?\$|INSERT .*?\$|UPDATE .*?\$|DELETE .*?\$
```

## 2. Find concatenated SQL

```
regex
(SELECT|INSERT|UPDATE|DELETE).*\.\s*\$
```
Finds string concatenation like:
```
"SELECT * FROM users WHERE id = " . $id
```

### 3. Find direct query execution
```
->query\s*\(|mysqli_query\s*\(
```

These are the execution points you’ll need to review.

### 4. Find request inputs
```
\$_GET|\$_POST|\$_REQUEST|\$_SERVER
```

Cross-reference these with SQL usage.

### 5. Find session usage
```
\$_SESSION
```

Lower risk, but still worth reviewing (like your case).

### 6. Find dangerous dynamic SQL parts
```
ORDER BY .*?\$|LIMIT .*?\$|IN\s*\(.*?\$|FROM .*?\$|JOIN .*?\$
```

These cannot be fixed with prepared statements → need allowlists.

### Visual Studio Code Find and Replace

* 1

```
$accountid = $_SESSION['account_id'];
```

Replace with:

```
$accountid = $_SESSION['account_id'] ?? null;
if (!is_int($accountid) && !ctype_digit($accountid)) {
    exit('Invalid account ID');
}
$accountid = (int)$accountid;
```

* 2

```
$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);
```

Replace with:

```
$stmt = $con->prepare("SELECT * FROM accesscontrol WHERE accountID = ?");
$stmt->bind_param("i", $accountid); // "i" = integer
$stmt->execute();
$resultAccess = $stmt->get_result();
```

* 3

```
$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);
```

Replace with:

```
$sqluser = "SELECT * FROM accounts WHERE id = ?";
$stmt = $con->prepare($sqluser);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $accountid);
$stmt->execute();
$resultuser = $stmt->get_result();
```

* 4

```

```

Replace with:

```

```