<?php
//die("Not Authorized");
require_once DOC_DIR.'core/b2b/b2b_copy/lib/Credentials.php';
require_once DOC_DIR.'core/b2b/b2b_copy/lib/Database.php';
require_once DOC_DIR.'core/b2b/b2b_copy/lib/MyApi.php';
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
    <input type="Submit" name="btnContact" value="Contacts" />
    <input type="Submit" name="btnItem" value="Items" />
    <input type="Submit" name="btnInvoice" value="Invoices" />
</form>
<?php
if($_POST) {
    $api = new MyApi($db);
    if(isset($_POST['btnContact'])) {
        $response = $api->postContacts($credentials);
        echo $response;
    }
    if(isset($_POST['btnItem'])) {
        $response = $api->postItems($credentials);
        echo $response;
    }
    if(isset($_POST['btnInvoice'])) {
        $response = $api->postInvoices($credentials);
        echo $response;
    }
}
?>
</div>
</body>
</html>  
