<?php
require_once __DIR__.'/DB.php';
require_once __DIR__.'./../model/User.php';
require_once __DIR__.'./../model/Tweet';
require_once __DIR__.'./../model/Comment.php';
require_once __DIR__.'./../model/formatDate.php';
require_once __DIR__.'./../model/Message.php';

session_start();

class Controller
{


    private function render($template,$data = [])
    {
        $html = file_get_contents(__DIR__ . "./../templates/$template.html");
        foreach ($data as $key => $value) {
            $html = str_replace('{{'.$key.'}}',$value,$html);
        }
        return $html;
    }
    public function createUser(){

        DB::init();

        $user = new User();
        $user->setEmail($_POST['email']);
        $user->setHashPass($_POST['password']);

        try {
            $user->saveToDB(DB::$conn);
            header('Location: /login');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function loginCheck(){

        DB::init();

        $email = $_POST['email'];
        $plain = $_POST['password'];

        if(strlen($email) * strlen($plain) === 0){
            throw new \Exception('Pass and email cannot be empty');
        }
        $user = User::loadByEmail(DB::$conn,$email);
        var_dump($user);
        if(!$user) {
            throw new \Exception("User does not exists");
        }
        if(password_verify($plain,$user->getHashPass())){

            $_SESSION['user'] = $user->getId();
            $_SESSION['logged'] = true;
            header('Location: /tweety'); return "";
        } else {
            $_SESSION['logged'] = false;
            return $this->showLogin();
        }

    }
    public function existingUser()
    {
        if(session_status() == PHP_SESSION_NONE){

        } else {
            if(isset($_SESSION['user']) && strlen($_SESSION['user']) > 0 &&
                isset($_SESSION['logged']) && $_SESSION['logged'] === true
            ){
                return true;
            }
        }
        return false;
    }

    public function showProfile()
    {
        DB::init();

        if(isset($_SESSION["user"])) {

            $tab = Tweet::loadAllTweetsByUserId(DB::$conn, $_SESSION["user"]);
            $user = User::loadById(DB::$conn, $_SESSION["user"]);
            echo $this->render('profile',$user->toArray());
            foreach ($tab as $key => $value){
                $this->showTweet(array(
                    'email' => $user->getEmail(),
                    'text' => $value->getMessage(),
                    'date' => $value->getCreationDate(),
                    'id' => $value->getId()
                    )
                );
            }
        } else {
            header("Location: /login");
            return "";
        }
    }

    public function updateProfile(){
        DB::init();

        if(isset($_SESSION["user"]))
        {
            $email = $_POST['email'];
            $user = User::loadById(DB::$conn,$_SESSION['user']);
            $user->setEmail($email);
            $user->setDirectPass($user->getHashPass());
            $user->saveToDB(DB::$conn);
            return $this->showProfile();
        }
        header("Location: /login");return"";
    }

    public function logout(){
        unset($_SESSION['user']);
        unset($_SESSION['logged']);
        session_unset();

        header('Location: /login'); return "";
    }
    public function showLogin()
    {
        if(!isset($_SESSION['user']))
        {
            return $this->render('login');
        } else {
            header('Location: /tweety');
        }
    }
    public function addTweet(){
        if(isset($_SESSION['user'])){
            DB::init();
            $text = $_POST['description'];
            $tweet = new Tweet();
            $tweet->setMessage($text);
            $tweet->setUserId($_SESSION['user']);
            $tweet->saveToDB(DB::$conn);
            header("Location: /tweety"); return"";
        }
        return NULL;
    }
    public function tweets(){
        if(isset($_SESSION['user'])){
            DB::init();
            echo $this->render('tweety');
            $tab = Tweet::loadAllTweets(DB::$conn);
            echo '<form action="" method="post">';
            foreach ($tab as $key => $value){
                $user = User::loadById(DB::$conn, $value->getUserId());
                $this->showTweet(array(
                    'email' => $user->getEmail(),
                    'text' => $value->getMessage(),
                    'date' => $value->getCreationDate(),
                    'id' => $value->getId(),
                ));
            }
            echo '</form>';

        } else {
            header('Location: /login'); return "";
        }
    }
    public function showTweet($tab)
    {
        echo '<div class="panel-body">';
        echo '<li class="list-group-item">';
        echo '    <div class="media row">
                    <div class="media-left">
                        <img src="./../images/img_avatar.png" class="media-object img-thumbnail" style="width:60px">
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">' . $tab['email']. '</h4>
                        <p>' . $tab['text'] . '</p><hr>
                        <a href="/usertweet.php?id='. $tab['id'] .'" class="btn btn-primary btn-xs">Commants 
                        <span class="badge">'.Comment::countOfCommentsByPostId(DB::$conn,$tab['id']).'</span>
                        </a>
                        <a href="" class="btn btn-success btn-xs">Follow</a><span class="text-muted"><small><i> '.formatDate::format($tab['date']).'</i></small></span>
                    </div>
                  </div>';
        echo'</li>';
        echo'</div>';
    }
    public function showComment($tab)
    {
        echo "<div class=container>";
        echo '<div class="media">
                    <div class="media-body">
                        <h4 class="media-heading">' . $tab['email']. '<small><i> ' . $tab['date'] . '</i></small></h4>
                        <p>' . $tab['text'] . '</p><hr>
                    </div>
                  </div>';
        echo '</div>';
    }
    public function showBoxMessage($tab){
        $status = ($tab['status'] === 0) ? "Not Read" : "Read";
        $class = ($tab['status'] === 0) ? "warning" : "";
        echo '<tr class="'.$class.'">';
        echo '<td>'. $tab['sender'] .'</td><td>'. $tab['date'] .'</td><td>'.$status.'</td><td><a href="?method=read&id='.$tab['id'].'">Show</a></td>';
        echo '</tr>';

    }

    /**
     * @param $tab
     */
    public function showBoxMessageText($tab){
        $status = ($tab['status'] === 0) ? "Not Read" : "Read";
        echo '<td>'. $tab['sender'] .'</td><td>'. $tab['date'] .'</td><td>'.$status.'</td><td><a href="./prvbox.php">Message Box</a></td>';
        echo '</table>';
        echo '<div class="row">';
            echo '<h3>Text:</h3>';
            echo '<div>'.$tab['text'].'</div>';
        echo '</div>';
    }
    public function showForm(){
        if(isset($_SESSION['user'])) {
            return $this->render('comment');
        } else {
            return"";
        }
    }
    public function showRegister()
    {
        if(!isset($_SESSION['user']))
        {
            return $this->render('register');
        } else {
            header('Location: /tweety');
        }
    }
    public function sendMessage(){

    }
    public function showHead(){
        return $this->render('head');
    }

    public function showNav(){
        return $this->render('nav');
    }

    public function showScripts(){
        return $this->render('scripts');
    }
    public function showFooter(){
        return $this->render('footer');
    }
    public function showMessageTop($i){

       if($i == 1){
           return $this->render('box');
       } elseif($i == 2) {
           return $this->render('box2');
       }
    }
    public function formBoxMessage(){
        return $this->render('formBoxMessage');
    }

}