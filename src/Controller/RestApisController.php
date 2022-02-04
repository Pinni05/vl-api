<?php
    declare(strict_types=1);
    namespace App\Controller;
    use Cake\Core\Configure;

    class RestApisController extends AppController
    {
        public function initialize(): void
        {
            parent::initialize();
            $this->loadComponent('RequestHandler');
            $this->loadComponent('Common');
        }
        public function setConditions($requestVars){
            $conditions= [];

            if (isset($requestVars['id'])) {
                $conditions[] = ['id'=>$requestVars['id']];
            }
            // if user entered text and is not empty, try to match the words
            if (isset($requestVars['query']) && !empty($requestVars['query'])) {

                $parsedText = $this->_parseText($requestVars['query']);
                $conditionQuery = array("OR"=>
                    array (
                        $parsedText['title'],
                        $parsedText['description'],
                        $parsedText['notes'],
                    )
                );

                $conditions["OR"][] = [$conditionQuery];
            }


            if(isset($requestVars['alphabet'])){
                $conditions[] = ['title LIKE' => $requestVars['alphabet']. '%'];
            }
            if(isset($requestVars['CategoryID'])){
                $conditions[] = ['CategoryID' => (int) $requestVars['CategoryID']];
            }
            if(!isset($requestVars['type'])) {
                $conditions[] = ['type' =>'All'];
            }else if(isset($requestVars['type'])  && $requestVars['type'] != 'All' )   {
                $conditions[] = ['type' =>$requestVars['type']];
            }
            // loop through and/or rows advanced search
            for($i=0; $i<10; $i++) {

                if (isset($requestVars['textsearch'.$i]) && !empty($requestVars['textsearch'.$i])) {

                    $parsedText = $this->_parseText($requestVars['textsearch'.$i]);


                    $conditionsSet[$i] = array("OR"=>
                        array (
                            $parsedText['title'],
                            $parsedText['description'],
                            $parsedText['notes'],
                        )
                    );

                    if($i==0)
                        $conditions["OR"][] = [$conditionsSet[$i]];
                    else if($i>0)
                        $conditions[$requestVars['textandor'.$i]][] = [$conditionsSet[$i]];

                }
            }

            return $conditions;

        }
        public function index()
        {

            $requestVars = $this->request->getQuery();
            $collections = array();
            if(isset($requestVars['token_key']) && ! empty($requestVars['token_key'])){
                if(!$this->CheckToken($requestVars['token_key'])){
                    $data['status'] = 'Error';
                    $data['message'] = 'Token Missmatch';
                    $data['collections'] = $collections;
                }else{

                    $conditions=$this->setConditions($requestVars);
                    if(isset($requestVars['type']) && in_array($requestVars['type'], array('All','eBook','eJournal', 'Database'), true)==false) {
                        $data['status'] = 'Error';
                        $data['message'] = 'Invalid search criteria passed in the URL, please try again';
                        $data['collections'] = $collections;
                    }else{
                        $collections = $this->fetchTable('Collections')->find()->where($conditions)->all();
                        $data['status'] = 'Success';
                        $data['message'] = '';
                        $data['collections'] = $collections;
                    }
                }
            }else{
                $data['status'] = 'Error';
                $data['message'] = 'Token Required';
                $data['collections'] = $collections;
            }



            $this->set('data', $data);
            $this->viewBuilder()->setOption('serialize', ['data']);
        }
        /*
        Parse text into an array format that will work with the Paginator helper $this->paginate['conditions']
        - If separated by a comma, split them into "compound words" (may contain one or more words)
        - If "compound words" are enclosed in double quotes "", it is an exact phrase match
        - If "compound words" are NOT enclosed in double quotes "", then split them into separate words
        */
        private function _parseText($text) {
            $text = urldecode(trim($text));
            $splitWords = explode(',', $text);


            foreach($splitWords as $word) {
                $word = trim($word);

                //Determine if exact phrase, ie. enclosed by ""
                $exactPhraseArray = explode('"', $word);
                if(empty($exactPhraseArray[0]) && empty($exactPhraseArray[0]))
                {
                    $exactPhrase = true;
                }
                else
                {
                    $exactPhrase = false;
                }

                if($exactPhrase) {
                    $titleArray["OR"][] = array("title LIKE" => "%". $exactPhraseArray[1] ."%");
                    $descriptionArray["OR"][] = array("description LIKE" => "%". $exactPhraseArray[1] ."%");
                    $notesArray["OR"][] = array("notes LIKE" => "%". $exactPhraseArray[1] ."%");
                }
                else {
                    $textArray = explode(' ', $word);
                    foreach ($textArray as $value) {
                        $titleArray["OR"][] = array("title LIKE" => "%". $value ."%");
                        $descriptionArray["OR"][] = array("description LIKE" => "%". $value ."%");
                        $notesArray["OR"][] = array("notes LIKE" => "%". $value ."%");
                    }
                }
            }

            $result = array('title'=>$titleArray, 'description'=>$descriptionArray,'notes'=>$notesArray);

            return $result;


        }
        private function CheckToken($token){
            $vl_token= Configure::read('VirtualLibrary.Token');
            $status = false;
            if($vl_token == $token){
                $status =  true;
            }
            return $status;

        }
        public function vlAuth(){
           $result    =   [];
           if ($this->request->is(['post']))
           {
                // Initialize vars
                $browserMessage = "DONE\n";
                $data = $this->request->getData();
                $username = trim($data['user']);
                $password = trim($data['pass']);

                if (filter_var($username, FILTER_VALIDATE_EMAIL)) {

                    $isAuthenticated = $this->fetchTable('MdlUsers')->checkUser($username, $password);
                    $loginStatus = ($isAuthenticated===1)?"Successful login attempt":"Un-successful login attempt";
                    $browserMessage = ($isAuthenticated===1)?"+VALID\n":"INVALID\n";

                    // Add login timestamp (whether successful or unsuccessful)
                    $this->fetchTable('LoginHistory')->addLoginHistory($username,$loginStatus);

                }
               $result['message']=$browserMessage;
                // Send result to browser


            }else{
               $result['message']='Missing username/password';
           }
            $this->set('data', $result);
            $this->viewBuilder()->setOption('serialize', ['data']);
        }
    }
