<?php
die("Not Authorized");
require __DIR__ . '/lib/Credentials.php';
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/MyApi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>B2B</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
<div style="padding:2em;">
<form method="post" style="margin-bottom:1em;">
    <div class="col-5">
        <table class="table">
            <tr>
                <td><input type="Submit" name="btnGetContacts" value="Get Contacts" /></td>
                <td><input type="Submit" name="btnCreateContacts" value="Create Contacts" /></td>
                <td><input type="Submit" name="btnUpdateContact" value="Update Contact" /></td>
            </tr>
            <tr>
                <td><input type="Submit" name="btnGetItems" value="Get Items" /></td>
                <td><input type="Submit" name="btnCreateItems" value="Create Items" /></td>
                <td><input type="Submit" name="btnUpdateItem" value="Update Item" /></td>
            </tr>
            <tr>
                <td><input type="Submit" name="btnGetInvoices" value="Get Invoices" /></td>
                <td><input type="Submit" name="btnCreateInvoices" value="Create Invoices" /></td>
                <td><input type="Submit" name="btnUpdateInvoice" value="Update Invoice" /></td>
            </tr>
        </table>
    </div>
</div>
</form>
<?php
if($_POST) {
    $api = new MyApi($db);

    if(isset($_GET['btnGetContacts'])) {
        $response = $api->getContacts($credentials);
        echo $response;
    }
    if(isset($_POST['btnCreateContacts'])) {
        $response = $api->createContacts($credentials);
        echo $response;
    }
    if(isset($_POST['btnUpdateContact'])) {
        $response = $api->updateContact($credentials);
        echo $response;
    }

    if(isset($_POST['btnGetItems'])) {
        $response = $api->getItems($credentials);
        echo $response;
    }
    if(isset($_POST['btnCreateItems'])) {
        $response = $api->createItems($credentials);
        echo $response;
    }
    if(isset($_POST['btnUpdateItem'])) {
        $response = $api->updateItem($credentials);
        echo $response;
    }

    if(isset($_GET['btnGetInvoices'])) {
        $response = $api->getInvoices($credentials);
        echo $response;
    }
    if(isset($_POST['btnCreateInvoices'])) {
        $response = $api->createInvoices($credentials);
        echo $response;
    }
    if(isset($_POST['btnUpdateInvoice'])) {
        $response = $api->updateInvoice($credentials);
        echo $response;
    }
}
?>
</div>
</body>
</html>  
