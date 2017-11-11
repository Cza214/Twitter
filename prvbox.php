<?php

require(__DIR__.'/src/Controller.php');

if(!isset($_SESSION['user'])){
    header("Location: ./login");
}

echo '<html>';
$controller = new Controller();
echo $controller->showHead();
echo '<body>';
DB::init();

if($_SERVER['REQUEST_METHOD'] === "GET") {

    if (isset($_GET['method'])) {
        if ($_GET['method'] == 'read') {
            echo $controller->showMessageTop(1);
            if (isset($_GET['id'])) {
                $obiect = Message::loadMessageById(DB::$conn, $_GET['id']);
                $senderName = User::loadById(DB::$conn, $obiect->getSenderId());
                if($obiect->getReceiverId() == $_SESSION['user']) $obiect->saveToDB(DB::$conn);
                $controller->showBoxMessageText(array(
                    'sender' => $senderName->getEmail(),
                    'text' => $obiect->getText(),
                    'date' => $obiect->getDate(),
                    'status' => $obiect->getStatus()
                ));
            } else {
                echo 'Wrong ID';
                die;
            }
        } elseif ($_GET['method'] == 'out') {
            echo $controller->showMessageTop(1);
            $tab = Message::loadMessageBySenderId(DB::$conn, $_SESSION['user']);
            foreach ($tab as $key => $value) {
                $senderName = User::loadById(DB::$conn, $value->getReceiverId());
                $controller->showBoxMessage(array(
                    'id' => $value->getId(),
                    'status' => $value->getStatus(),
                    'sender' => $senderName->getEmail(),
                    'date' => $value->getDate()
                ));
            }
        } elseif($_GET['method'] == 'send'){

            echo $controller->formBoxMessage();
        }
        } else {
            echo $controller->showMessageTop(2);
            $tab = Message::loadMessageByReceiverId(DB::$conn, $_SESSION['user']);
            foreach ($tab as $key => $value) {
                $senderName = User::loadById(DB::$conn, $value->getSenderId());
                $controller->showBoxMessage(array(
                    'id' => $value->getId(),
                    'status' => $value->getStatus(),
                    'sender' => $senderName->getEmail(),
                    'date' => $value->getDate()
                ));
            }
        }
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['receiver']) && isset($_POST['text'])){
        $message = new Message();
        $user = User::loadByEmail(DB::$conn,$_POST['receiver']);
        $message->setReceiverId($user->getId());
        $message->setText($_POST['text']);
        $message->setSenderId($_SESSION['user']);
        $message->saveToDB(DB::$conn);
    }

    header("Location: ./prvbox.php");
}