<?php
    /**
     * This model manipulates and resizes a user profile picture prior to saving to db
     *
     * After successfully uploading a picture the form data looks like this:
     * $this->request->data['User']['profile_picture']
     *   = ['name' => '3498.jpg','type' => 'image/jpeg','tmp_name' => 'C:\xampp_177\tmp\php3339.tmp','error' => (int) 0,'size' => (int) 6106);
     *
     * Usage in Controller:
     * $this->loadModel('Image');
     * $this->Image->resizeImage($this->request->data['User']['profile_picture'],145,145);
     *
     * Reference: I modified this code to work in cakephp2 based on http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
     *
     */
    namespace App\Controller\Component;

    use Cake\Controller\Component;

    class CommonComponent extends Component {

        public function checkUser($username, $password){

            if(!$this->_connected){
                return false;
            }

            // Execute a prepared statement by binding PHP variables
            $dbh = $this->_connection;

            $sth = $dbh->prepare('SELECT password FROM external_auth_active_mdl_users where username = :username');
            $sth->bindParam(':username', $username, PDO::PARAM_STR);
            $sth->execute();
            $result = $sth->fetch(PDO::FETCH_ASSOC);

            if(password_verify($password, $result['password'])) {
                $this->_validLogin = 1;
            }

            // Return result
            return $this->_validLogin;
        }

        /**
         * Adds login history in the virtual library database
         *
         * @param mixed $username
         */
        public function addLoginHistory($username,$loginStatus)
        {
            if(!$this->_connected){
                return false;
            }

            // Pass null to timestamp, the column defaults to CURRENT_TIMESTAMP in MySQL
            $stmt = $this->_connection->prepare("INSERT INTO login_history (username, logintimestamp, loginstatus) VALUES (:username, NULL, :loginstatus)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':loginstatus', $loginStatus);
            $stmt->execute();

            // Return result
            return true;

        }

    }
?>
