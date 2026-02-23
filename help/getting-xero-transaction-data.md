# Getting Xero transaction Data
In Xero’s Accounting API there are three related but distinct concepts you need to keep straight:

## 1) ACCPAY invoice (bill)

An **Invoice** with `Type = "ACCPAY"` is a supplier bill. In the API, invoices can include a **read-only** `Payments` **array** (plus `Prepayments` / `Overpayments`).
So at the data model level: **Invoice ⇄ Payment** is the primary link.

## 2) Payment (what ties a bill to a bank account)

A **Payment** is the object that represents money applied to an invoice/credit note/overpayment/prepayment. The payment record is what you use to answer:
* Which invoice did this pay?
* Which bank account was used?
* What date / amount / reference did it have?
Because your output needs **fields from both invoice and payment grouped by bank account**, the **Paymen**t endpoint is usually the best “spine” to build from.

## 3) Bank transaction (statement-line style transactions)

A **BankTransaction** is for “spent money / received money / transfers / overpayment/prepayment” style transactions.
Crucially, Xero’s **BankTransactions endpoint does *not* return payments applied to invoices**.
So if you start from BankTransactions, you will miss the invoice-payment activity you’re trying to report on.

## The practical relationship (what joins to what)
### Invoice ⇄ Payment

Payments are linked to invoices.
Invoices expose a `Payments` array (read-only).

### Payment ⇄ Bank account

Payments are made **from an Account** (a BANK-type account in the chart of accounts).
That’s the natural key to group on (`AccountID`, `Code`, `Name`, etc.).

### BankTransaction is parallel, not the source of invoice payments

Use BankTransactions only if you also want non-invoice bank activity.
Do not expect BankTransactions to provide the invoice payment join.

## Recommended query + loop order (for your exact requirement)
### A) Get bank accounts (for grouping headers / validation)

`GET /Accounts?where=Status=="ACTIVE" AND Type=="BANK"`
This gives you the bank accounts you’ll group under. (This is also useful for mapping `AccountID → display name`.)

### B) Pull Payments in the date range (this is your driver set)

`GET /Payments?where=Date>=DateTime(YYYY,MM,DD) AND Date<=DateTime(YYYY,MM,DD)`
Add whatever status filters you need (e.g., exclude deleted/voided depending on your reporting rules).
Page through results.
Now you can build:
`paymentsByAccountId[payment.account.accountId].append(payment)`
collect `invoiceIds` from each payment’s linked invoice.

### C) Bulk-fetch the invoices you actually need (join step)

`GET /Invoices?where=InvoiceID=Guid("...") OR InvoiceID=Guid("...") ...`
Fetch in chunks (because URL/where length limits and paging constraints).
Store in a map: `invoiceById[invoiceId] = invoice`

### D) Render grouped output

* For each bank account:
    * for each payment under that account:
        * look up the linked invoice
        * display combined fields (e.g., invoice number/contact/status/due date + payment date/amount/reference)

This order is efficient because:
* **Payments already contain the “bank account” dimension**, which is what you’re grouping by.
* You only fetch **invoices that are actually referenced by payments**.

## Edge cases you should plan for

### 1) Batch payments

Batch payments exist to bundle multiple bills/invoices into one bank statement line for reconciliation.
Depending on how you want to present them:
* you may still see multiple Payment records (one per invoice) even if they reconcile as a single batch on the statement side, or you may need to also pull BatchPayments for a “batch header”.

### 2) Prepayments / overpayments / credit notes

Bills can be settled via allocations from overpayments/prepayments/credit notes. Invoices still surface related objects (`Payments`, `Prepayments`, `Overpayments`).
If your report is “money out of bank accounts”, you’ll likely still focus on **Payments**; if you’re doing “settlement activity”, you may need to also interpret allocations.

### 3) “Why doesn’t this appear in Payments?”

If something was coded as “Spend Money” (BankTransaction) rather than “Pay Bill” (Payment against ACCPAY), it won’t join the same way. That’s not an API limitation—it’s different accounting workflows.

# Bottom line

For “**fields from both the invoice and the payment grouped by each bank account**”, the cleanest approach is:
Accounts (BANK) → Payments (group by AccountID, collect InvoiceIDs) → Invoices (bulk fetch) → join + render
…and only bring in **BankTransactions** if you also want non-invoice bank activity, because **BankTransactions won’t give you invoice payments**.