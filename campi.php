<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


# ?action=list -> get list of places
# ?action=edit -> edit a place
# ?action=new  -> add a new place

isset($_GET['action']) or die();

$action = $_GET['action'];

# list all the places in JSON format
if($action == 'list') {
   
    // check if method is GET 
    if($_SERVER['REQUEST_METHOD'] != 'GET')
        error(5, '?action=list require GET');
    // load database
    require_once('db.php');
    header("Content-type: application/json; charset=utf-8;");
    $data = query("SELECT * FROM place;");
    $data = json_encode($data);
    echo $data;
}

if($action == 'new' || $action == 'edit'){

    // check if method is POST
    if($_SERVER['REQUEST_METHOD'] != 'POST')
        error(1, '?action=edit and ?action=new require POST');

    // check required fields
    foreach(Array("name", "lat", "lng", "city", "address", "recaptcha_response_field", "recaptcha_challenge_field") as $item){
        if(!isset($_POST[$item]) || $_POST[$item] == "")
            error(4, $item.' missing'); 
    }

    // check if coordinates are numeric
    if(!is_numeric($_POST['lat']))
        error(6, 'latitude incorrect');
    if(!is_numeric($_POST['lng']))
        error(6, 'longitude incorrect');

    // check if captcha is correct
    require_once('recaptchalib.php');

    // IMPORTANT: YOU MUST INSERT YOUR OWN KEYS!
    $publickey = "";
    $privatekey = "";

    $resp = recaptcha_check_answer ($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);
    if (!$resp->is_valid) {
        error(2, 'reCAPTCHA invalid');
    }
   
    // additional check for edit 
    if ($action == 'edit') {    
        // check if place_id is provided
        if (!isset($_POST['place_id']) || empty($_POST['place_id']))
            error(4, 'place_id missing');

        // check if place_id exsist
        require_once('db.php');
        $exsist = query('SELECT * FROM place WHERE place_id = ?;', Array($_POST['place_id']));
        if (empty($exsist))
            error(6, 'place_id incorrect');
    }
 
    // from this point, the input is OK 
    require_once('db.php'); 

    $elements = Array(
        "place_id",
        "name",
        "lat",
        "lng",
        "city",
        "address",
        "referent",
        "tel",
        "web",
        "email",
        "beds",
        "fee",
        "wc",
        "water",
        "electricity",
        "shopadvices",
        "notes"
    );

    $new = Array();

    // prepare new element as array
    foreach($elements as $item) {
        $new[$item] = (!isset($_POST[$item]) || empty($_POST[$item])) ? "" : $_POST[$item];
    }
    
    if($action == 'new')
        $new['place_id'] = new_id();

    $new['submission_date'] = time();

    // add entry in the database
    query("INSERT INTO place (submission_date, place_id, name, lat, lng, city, address, referent, tel, web, email, beds, fee, wc, water, electricity, shopadvices, notes) VALUES (:submission_date, :place_id, :name, :lat, :lng, :city, :address, :referent, :tel, :web, :email, :beds, :fee, :wc, :water, :electricity, :shopadvices, :notes);",
           $new);
}

function new_id() {
    $place_id = query('SELECT MAX(place_id) AS place_id FROM place;');
    $place_id = $place_id[0]['place_id'];
    return $place_id + 1;
}

function error($code, $message) {
    header('Content-type: application/json; charset=utf-8;');
    echo json_encode(Array("error"=>true, "code"=>$code, "message"=>$message));
    die();
}


?>
