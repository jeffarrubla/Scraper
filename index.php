<?php
//$dbh = new PDO('mysql:host=localhost;dbname=scraper', 'root', 'rootpassword');

$langs = array('php', 'html5', 'javascript', 'jquery', 'css3', 'python','java', 'ruby', 
                'ruby on rails', 'c++', 'git', 'github', 'node.js', 'django');

foreach ($langs as $language) {   

    //$language = 'python';
    $url = 'http://stackoverflow.com/questions/tagged/'. urlencode($language) .'?sort=votes&pagesize=50';
    $output = file_get_contents($url);
    //Get the 50 titles
    preg_match_all('/<h3>(.*)<\/h3>/i', $output, $title); 
    $title = $title[1];
    $i = 1;

    if(!empty($title)){

        $dbh->beginTransaction(); 
        $query = $dbh->prepare("INSERT INTO content (`URL`, `language`, `title_en`, `en`) VALUES (:a, :b, :c, :d)");

        foreach ($title as $value ) {
            //create the URL for each question
            $value = str_replace("/questions", "http://stackoverflow.com/questions", $value) ;
            //Get the URL
            preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $value, $matches);
            //Get the Title
            preg_match_all('/<a(.*?)href="(.*)">(.*)<\/a>/', $value, $head);    

            //Read the Question
            $subUrl = file_get_contents( $matches[2][0] );
            //Get the post description
            preg_match_all('/<div class=\"post\-text\" itemprop=\"description\">(.*?)<\/div>/s',$subUrl,$body);

            //Prepare for insert
            $query->bindParam(':a', $matches[2][0] );
            $query->bindParam(':b', $language );
            $query->bindParam(':c', $head[3][0] );
            $query->bindParam(':d', $body[0][0] );

             try {
                 $query->execute();
                 echo $i .'<br>';
              } catch (PDOException $e){
                echo $e->getMessage();
              }   
            $i++;
        }

        $dbh->commit(); 
    }
}
?>