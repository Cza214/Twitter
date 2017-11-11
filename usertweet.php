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
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    if(isset($_GET['id'])){
        echo '<br>';
        $tweet = Tweet::loadTweetById(DB::$conn, $_GET['id']);
        $user = User::loadById(DB::$conn,$tweet->getUserId());
        $controller->showTweet(array(
            'email' => $user->getEmail(),
            'text' => $tweet->getMessage(),
            'date' => $tweet->getCreationDate(),
            'id' => $tweet->getId()
        ));
        echo '<div class="container"><h3>Comments: </h3></div>';
        $controller->showScripts();
        $comments = Comment::loadAllCommentsByPostId(DB::$conn,$_GET['id']);
        foreach($comments as $key => $value){
            $user = User::loadById(DB::$conn,$value->getUserId());
            $controller->showComment(array(
                  'id' => $value->getId(),
                  'date' => $value->getCreationDate(),
                  'text' => $value->getText(),
                  'email' => $user->getEmail()
              )
            );
        }
    }
} elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_GET['id'])){
        $text = $_POST['comment'];
        $tweet = Tweet::loadTweetById(DB::$conn, $_GET['id']);
        $user = User::loadById(DB::$conn,$tweet->getUserId());
        $comm = new Comment();
        $comm->setText($text);
        $comm->setPostId($_GET['id']);
        $comm->setUserId($_SESSION['user']);
        $comm->saveToDB(DB::$conn);
    }
    header("Location: ".$_SERVER['REQUEST_URI']);
}
echo $controller->showForm();
echo $controller->showScripts();
echo '</html>';
