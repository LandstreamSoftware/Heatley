<?php
/**
 * Single-file PHP form + POST to Power Automate HTTP trigger
 * - Renders an HTML form (GET)
 * - On submit (POST), builds JSON payload (including epoch-ms date_of_incident) and sends it to the Flow URL
 *
 * Requirements:
 * - PHP 7.4+ recommended
 * - cURL extension enabled
 */

$flowUrl = 'https://7739c9d9bc764bdfb4c09ff94c1e73.33.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/e05c4982dd484d8aa6d7a9e95df07556/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=3aPJXNJQ86k4PsTWbSwfcR7nOKi4X3quxr9xN9B0DvM';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/**
 * Convert a YYYY-MM-DD string to epoch milliseconds (UTC midnight).
 * Returns null if empty/invalid.
 */
function dateToEpochMs(?string $ymd): ?int {
    $ymd = trim((string)$ymd);
    if ($ymd === '') return null;

    // Basic validation: YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) return null;

    $dt = DateTime::createFromFormat('!Y-m-d', $ymd, new DateTimeZone('UTC'));
    if (!$dt) return null;

    return (int)($dt->getTimestamp() * 1000);
}

/**
 * Empty string => null; otherwise trimmed string.
 */
function nullIfEmpty(?string $v): ?string {
    $v = trim((string)$v);
    return ($v === '') ? null : $v;
}

$status = null;
$responseBody = null;
$error = null;
$sentPayloadPretty = null;

// Defaults (pre-fill)
$defaults = [
    'objectid' => '37',
    'which_group_are_you_associated' => '73a14f89-a414-ed11-b83d-00224891ef9f', // Campbells Bay Urban Sanctuary
    'first_name' => 'Barry',
    'last_name' => 'Pyle',
    'email_address' => 'barrygpyle@gmail.com',
    'phone_number' => '0274420153',
    'year_of_birth' => '1962',
    'person_reporting_this_incident' => 'Barry',
    'date_of_incident_ymd' => '2026-01-20', // user-friendly date input, converted to epoch-ms
    'time_of_incident' => '09:47',
    'location_address' => '65 Gulf View Road',
    'activity' => '909320000',
    'activity_other' => '',
    'accident_or_near_miss' => '909320001',
    'was_there_an_injury' => 'Yes',
    'select_an_injury_type' => '909320002',
    'injury_type_other' => '',
    'description_of_what_happened' => 'fell over',
    'what_caused_the_near_miss_or_ac' => 'tripped',
    'seriousness_of_incident' => '909320000',
    'how_often_is_this_likely_to_hap' => '909320000',
    'damage_to_property' => 'Yes',
    'what_was_damaged' => 'track',
    'nature_of_damage' => 'misc',
    'what_caused_the_damage' => 'testing',
    'was_any_treatment_given' => 'Yes',
    'type_of_treatment_given' => '909320002',
    'name_of_person_who_provided_tre' => 'Barry Pyle',
    'medical_centre_visited' => 'Waiake',
    'suggested_actions_to_prevent_re' => 'care',
    'comments' => 'My comments',
    'manager_email' => 'barry.pyle@econet.nz',
];

$values = $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Merge posted values into $values for re-display on error
    foreach ($defaults as $k => $v) {
        if (isset($_POST[$k])) {
            $values[$k] = is_string($_POST[$k]) ? $_POST[$k] : $v;
        }
    }

    // Build payload exactly matching the example schema/keys
    $payload = [
        "which_group_are_you_associated" => nullIfEmpty($values['which_group_are_you_associated']),
        "first_name" => nullIfEmpty($values['first_name']),
        "last_name" => nullIfEmpty($values['last_name']),
        "email_address" => nullIfEmpty($values['email_address']),
        "phone_number" => nullIfEmpty($values['phone_number']),
        "year_of_birth" => ($values['year_of_birth'] === '' ? null : (is_numeric($values['year_of_birth']) ? (int)$values['year_of_birth'] : null)),
        "person_reporting_this_incident" => nullIfEmpty($values['person_reporting_this_incident']),
        "date_of_incident" => dateToEpochMs($values['date_of_incident_ymd']),
        "time_of_incident" => nullIfEmpty($values['time_of_incident']),
        "location_address" => nullIfEmpty($values['location_address']),
        "activity" => nullIfEmpty($values['activity']),
        "activity_other" => nullIfEmpty($values['activity_other']),
        "accident_or_near_miss" => nullIfEmpty($values['accident_or_near_miss']),
        "was_there_an_injury" => nullIfEmpty($values['was_there_an_injury']),
        "select_an_injury_type" => nullIfEmpty($values['select_an_injury_type']),
        "injury_type_other" => nullIfEmpty($values['injury_type_other']),
        "description_of_what_happened" => nullIfEmpty($values['description_of_what_happened']),
        "what_caused_the_near_miss_or_ac" => nullIfEmpty($values['what_caused_the_near_miss_or_ac']),
        "seriousness_of_incident" => nullIfEmpty($values['seriousness_of_incident']),
        "how_often_is_this_likely_to_hap" => nullIfEmpty($values['how_often_is_this_likely_to_hap']),
        "damage_to_property" => nullIfEmpty($values['damage_to_property']),
        "what_was_damaged" => nullIfEmpty($values['what_was_damaged']),
        "nature_of_damage" => nullIfEmpty($values['nature_of_damage']),
        "what_caused_the_damage" => nullIfEmpty($values['what_caused_the_damage']),
        "was_any_treatment_given" => nullIfEmpty($values['was_any_treatment_given']),
        "type_of_treatment_given" => nullIfEmpty($values['type_of_treatment_given']),
        "name_of_person_who_provided_tre" => nullIfEmpty($values['name_of_person_who_provided_tre']),
        "medical_centre_visited" => nullIfEmpty($values['medical_centre_visited']),
        "suggested_actions_to_prevent_re" => nullIfEmpty($values['suggested_actions_to_prevent_re']),
        "comments" => nullIfEmpty($values['comments']),
        "manager_email" => nullIfEmpty($values['manager_email']),
    ];

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $sentPayloadPretty = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    if ($json === false) {
        $error = "Failed to encode JSON: " . json_last_error_msg();
    } else {
        // Send via cURL
        $ch = curl_init($flowUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            // Optional: timeouts
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $responseBody = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrNo) {
            $error = "cURL error ({$curlErrNo}): {$curlErr}";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Incident Report → Power Automate</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; line-height: 1.35; }
    .wrap { max-width: 980px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; }
    label { font-weight: 600; display: block; margin-bottom: 6px; }
    input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], input[type="time"], select, textarea {
      width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;
      box-sizing: border-box;
    }
    textarea { min-height: 90px; }
    .full { grid-column: 1 / -1; }
    .actions { margin-top: 18px; display: flex; gap: 12px; align-items: center; }
    button { padding: 10px 14px; border: 0; border-radius: 6px; background: #1b5cff; color: #fff; cursor: pointer; }
    button.secondary { background: #666; }
    .card { background: #f7f7f7; border: 1px solid #e3e3e3; border-radius: 10px; padding: 14px; margin-top: 18px; }
    pre { background: #111; color: #eee; padding: 12px; border-radius: 8px; overflow: auto; }
    .ok { color: #0a7a21; font-weight: 700; }
    .bad { color: #b00020; font-weight: 700; }
    @media (max-width: 760px) { .grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<div class="wrap">
  <h1>Incident Report</h1>
  <p>This form posts JSON to the configured Power Automate HTTP trigger.</p>

  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="card">
      <div>
        <?php if ($error): ?>
          <div class="bad">Request failed</div>
          <p><?= h($error) ?></p>
        <?php else: ?>
          <div class="<?= ($status >= 200 && $status < 300) ? 'ok' : 'bad' ?>">
            HTTP Status: <?= h((string)$status) ?>
          </div>
          <p>Response (raw):</p>
          <pre><?= h((string)$responseBody) ?></pre>
        <?php endif; ?>
      </div>

      <p>Payload sent:</p>
      <pre><?= h((string)$sentPayloadPretty) ?></pre>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="grid">
      <div class="full">
        <label for="which_group_are_you_associated">Which group are you associated</label>
        <input type="text" id="which_group_are_you_associated" name="which_group_are_you_associated" value="<?= h($values['which_group_are_you_associated']) ?>">
      </div>

      <div>
        <label for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="<?= h($values['first_name']) ?>">
      </div>

      <div>
        <label for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="<?= h($values['last_name']) ?>">
      </div>

      <div>
        <label for="email_address">Email address</label>
        <input type="email" id="email_address" name="email_address" value="<?= h($values['email_address']) ?>">
      </div>

      <div>
        <label for="phone_number">Phone number</label>
        <input type="tel" id="phone_number" name="phone_number" value="<?= h($values['phone_number']) ?>">
      </div>

      <div>
        <label for="year_of_birth">Year of birth (optional)</label>
        <input type="number" id="year_of_birth" name="year_of_birth" min="1900" max="2100" value="<?= h($values['year_of_birth']) ?>">
      </div>

      <div>
        <label for="person_reporting_this_incident">Person reporting this incident (optional)</label>
        <input type="text" id="person_reporting_this_incident" name="person_reporting_this_incident" value="<?= h($values['person_reporting_this_incident']) ?>">
      </div>

      <div>
        <label for="date_of_incident_ymd">Date of incident</label>
        <input type="date" id="date_of_incident_ymd" name="date_of_incident_ymd" value="<?= h($values['date_of_incident_ymd']) ?>">
      </div>

      <div>
        <label for="time_of_incident">Time of incident</label>
        <input type="time" id="time_of_incident" name="time_of_incident" value="<?= h($values['time_of_incident']) ?>">
      </div>

      <div class="full">
        <label for="location_address">Location address</label>
        <input type="text" id="location_address" name="location_address" value="<?= h($values['location_address']) ?>">
      </div>

      <div>
        <label for="activity">Activity (code)</label>
        <input type="number" id="activity" name="activity" value="<?= $values['activity'] ?>">
      </div>

      <div>
        <label for="activity_other">Activity other (optional)</label>
        <input type="text" id="activity_other" name="activity_other" value="<?= h($values['activity_other']) ?>">
      </div>

      <div>
        <label for="accident_or_near_miss">Accident or near miss (code)</label>
        <input type="text" id="accident_or_near_miss" name="accident_or_near_miss" value="<?= h($values['accident_or_near_miss']) ?>">
      </div>

      <div>
        <label for="was_there_an_injury">Was there an injury (optional)</label>
        <input type="text" id="was_there_an_injury" name="was_there_an_injury" value="<?= h($values['was_there_an_injury']) ?>">
      </div>

      <div>
        <label for="select_an_injury_type">Select an injury type (optional)</label>
        <input type="text" id="select_an_injury_type" name="select_an_injury_type" value="<?= h($values['select_an_injury_type']) ?>">
      </div>

      <div>
        <label for="injury_type_other">Injury type other (optional)</label>
        <input type="text" id="injury_type_other" name="injury_type_other" value="<?= h($values['injury_type_other']) ?>">
      </div>

      <div class="full">
        <label for="description_of_what_happened">Description of what happened (optional)</label>
        <textarea id="description_of_what_happened" name="description_of_what_happened"><?= h($values['description_of_what_happened']) ?></textarea>
      </div>

      <div class="full">
        <label for="what_caused_the_near_miss_or_ac">What caused the near miss or accident</label>
        <textarea id="what_caused_the_near_miss_or_ac" name="what_caused_the_near_miss_or_ac"><?= h($values['what_caused_the_near_miss_or_ac']) ?></textarea>
      </div>

      <div>
        <label for="seriousness_of_incident">Seriousness of incident (code)</label>
        <input type="text" id="seriousness_of_incident" name="seriousness_of_incident" value="<?= h($values['seriousness_of_incident']) ?>">
      </div>

      <div>
        <label for="how_often_is_this_likely_to_hap">How often is this likely to happen (code)</label>
        <input type="text" id="how_often_is_this_likely_to_hap" name="how_often_is_this_likely_to_hap" value="<?= h($values['how_often_is_this_likely_to_hap']) ?>">
      </div>

      <div>
        <label for="damage_to_property">Damage to property</label>
        <select id="damage_to_property" name="damage_to_property">
          <?php foreach (['No','Yes'] as $opt): ?>
            <option value="<?= h($opt) ?>" <?= ($values['damage_to_property'] === $opt) ? 'selected' : '' ?>><?= h($opt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="what_was_damaged">What was damaged (optional)</label>
        <input type="text" id="what_was_damaged" name="what_was_damaged" value="<?= h($values['what_was_damaged']) ?>">
      </div>

      <div>
        <label for="nature_of_damage">Nature of damage (optional)</label>
        <input type="text" id="nature_of_damage" name="nature_of_damage" value="<?= h($values['nature_of_damage']) ?>">
      </div>

      <div>
        <label for="what_caused_the_damage">What caused the damage (optional)</label>
        <input type="text" id="what_caused_the_damage" name="what_caused_the_damage" value="<?= h($values['what_caused_the_damage']) ?>">
      </div>

      <div>
        <label for="was_any_treatment_given">Was any treatment given</label>
        <select id="was_any_treatment_given" name="was_any_treatment_given">
          <?php foreach (['No','Yes'] as $opt): ?>
            <option value="<?= h($opt) ?>" <?= ($values['was_any_treatment_given'] === $opt) ? 'selected' : '' ?>><?= h($opt) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="type_of_treatment_given">Type of treatment given (optional)</label>
        <input type="text" id="type_of_treatment_given" name="type_of_treatment_given" value="<?= h($values['type_of_treatment_given']) ?>">
      </div>

      <div>
        <label for="name_of_person_who_provided_tre">Name of person who provided treatment (optional)</label>
        <input type="text" id="name_of_person_who_provided_tre" name="name_of_person_who_provided_tre" value="<?= h($values['name_of_person_who_provided_tre']) ?>">
      </div>

      <div>
        <label for="medical_centre_visited">Medical centre visited (optional)</label>
        <input type="text" id="medical_centre_visited" name="medical_centre_visited" value="<?= h($values['medical_centre_visited']) ?>">
      </div>

      <div class="full">
        <label for="suggested_actions_to_prevent_re">Suggested actions to prevent recurrence (optional)</label>
        <textarea id="suggested_actions_to_prevent_re" name="suggested_actions_to_prevent_re"><?= h($values['suggested_actions_to_prevent_re']) ?></textarea>
      </div>

      <div class="full">
        <label for="comments">Comments</label>
        <textarea id="comments" name="comments"><?= h($values['comments']) ?></textarea>
      </div>

      <div>
        <label for="manager_email">Manager Email</label>
        <input type="text" id="manager_email" name="manager_email" value="<?= h($values['manager_email']) ?>">
      </div>

    </div>

    <div class="actions">
      <button type="submit">Submit to Power Automate</button>
      <button type="button" class="secondary" onclick="window.location.href=window.location.pathname;">Reset</button>
    </div>
  </form>

  <div class="card">
    <strong>Notes</strong>
    <ul>
      <li><code>date_of_incident</code> is sent as epoch milliseconds derived from the date picker (UTC midnight).</li>
      <li>Blank fields are sent as <code>null</code> to match the example payload style.</li>
      <li>If your Flow expects different typing (e.g., numeric codes as integers), adjust the casting in <code>$payload</code>.</li>
    </ul>
  </div>
</div>
</body>
</html>
