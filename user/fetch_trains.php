<?php
define( 'WEB_PAGE_TO_ROOT', '../' );
require_once WEB_PAGE_TO_ROOT . 'include/Page.inc.php';

PageStartup( array( 'authenticated' ) );
    
DatabaseConnect();
if (checkPermissions($_SESSION['user_id'], 2) == "false") {
    header("HTTP/1.0 403 Forbidden");
    require_once WEB_PAGE_TO_ROOT . '404.php';
    exit();
}

if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $query = $db->prepare('SELECT t_number FROM trains WHERE t_date = :date');
    $query->bindParam(':date', $date, PDO::PARAM_STR);
    $query->execute();
    $trains = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($trains)) {
        echo json_encode(['status' => 'no_trains']);
    } else {
        echo json_encode(['status' => 'found', 'trains' => $trains]);
    }
}
?>
