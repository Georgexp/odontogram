<?php
ob_start();
require_once("../../globals.php");
require_once("$srcdir/forms.inc.php");

$pid = $_SESSION['pid'] ?? 0;
$encounter = $_SESSION['encounter'] ?? 0;
$user = $_SESSION['authUser'] ?? '';
$groupname = $_SESSION['authGroup'] ?? '';
$authorized = $_SESSION['userauthorized'] ?? 0;

if (!$pid || !$encounter) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error' => xl('Missing patient or encounter context')]);
    exit;
}

// Check if a form already exists in the forms table for this encounter
$existing_form = sqlQuery("SELECT id, form_id FROM forms WHERE encounter = ? AND formdir = 'odontogram' AND deleted = 0", [$encounter]);
$existing_form_id = $existing_form['id'] ?? null;
$form_id = $existing_form['form_id'] ?? null;

if (!$existing_form_id) {
     // Generate a new form_id using sequences
    $form_id = generate_id();

   // Register the form in the forms table
    sqlInsert(
        "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, formdir) 
         VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, 'odontogram')",
        [$encounter, "Odontogram", $form_id, $pid, $user, $groupname, $authorized]
    );
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'form_id' => $form_id]);
exit;
