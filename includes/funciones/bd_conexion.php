
<?php
$conn = new mysqli('localhost', 'root', 'root', 'encuentro_departamental', 8889);
if($conn->connect_error){
  echo $conn->connect_error;
}
?>

