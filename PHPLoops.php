<?php
$numbers = array(1, 2, 3, 4, 5);
$len=count($numbers);
for($i=0; $i<$len; $i++){
    //prints value if even
    if($numbers[$i]%2==0){
        echo "$numbers[$i] is an even number <br>";
    }
}

?>
