<?php


include_once "SCA/SCA.php";

$contact_service = SCA::getService('Contact.php');

$contact = $contact_service->retrieve(1);

echo '<p>';
print_r($contact);
echo '<p/>';

$id = $contact_service->create($contact);

echo '<p>';
echo 'Created: ' . $id;
echo '<p/>';

if ($contact_service->delete($id))
    echo 'DELETED';

echo '<p>';
print_r($contact);
echo '<p/>';

date_default_timezone_set('Europe/London');
$contact->updated = date('Y-m-j G-i-s');
$contact->shortname = "Fred";

echo '<p>';
if ($contact_service->update(1, $contact))
    echo 'UPDATED';
echo '</p>';
    
echo '<p>';
if ($contacts = $contact_service->enumerate()) {
    echo 'Returned ' . count($contacts->contact) . ' contacts.';
}
echo '</p>';

?>