<?php
use Joomla\CMS\Factory;
require_once 'translit/vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;

class ModTranslitHelper {

    public static function transliterateAjax (){
        require_once('translit/TranslitProcessor.php');
        $TranslitProcessor = new TranslitProcessor();
        
        $input_text = Factory::getApplication()->getInput()->get('text', '', 'string');
        $toVariant = Factory::getApplication()->getInput()->get('toVariant', 'crh-cyr', 'string');
        $translited = $TranslitProcessor->translate($input_text, $toVariant);
        return ['text' => $translited];
    } 
   
   
    public static function transliterateUploadedAjax (){
        
        
        $hash = Factory::getApplication()->getInput()->get('hash', '', 'string');
        $toVariant = Factory::getApplication()->getInput()->get('toVariant', 'crh-cyr', 'string');
        $part = Factory::getApplication()->getInput()->get('part', '0', 'string');
        
        $source_dir = JPATH_BASE."/media/docs/".$hash."/src/";
        $target_dir = JPATH_BASE."/media/docs/".$hash."/trg/";
         
        $source_files = scandir($source_dir);
        $target_files = [];
        foreach($source_files as &$source_file){
            if($source_file == '.' || $source_file == '..'){
                continue;
            }
            $extension = pathinfo($source_file, PATHINFO_EXTENSION);
            
            /*
            if($extension == 'rtf'){
                $source_file = self::convertToDOCX($source_file, $source_dir, 'rtf');
                $extension = 'docx';
            }*/
            if($extension == 'docx'){
                if($part == 0){
                    $total = self::explodeDOCX($source_file, $source_dir);
                    return ["is_finished" => false, 'total_parts' => $total];
                } else {
                    $dir = self::translitDOCX($source_file, $source_dir, $target_dir,  $toVariant, $part);
                    if($dir == 'error:too_large'){ return $dir;}
                    if(!$dir){ return ["is_finished" => false]; }
                    $target_files[] = $dir;
                }
            }
            if($extension == 'txt'){
                $dir = self::translitTXT($source_file, $source_dir, $target_dir,  $toVariant);
                if(!$dir){ return false; }
                $target_files[] = $dir;
            }
        }
        $result = self::makeZip($target_files, $target_dir, $hash);
        if($result){
            return [
                "is_finished" => true,
                "result" => "/media/docs/".$hash."/trg/".$hash.".zip"
            ];
        }
        return ["is_finished" => false];
    }
    
    
    private static function translitDOCX($filename, $source_dir, $target_dir, $toVariant, $part) {
        $template_file_name = $source_dir.'/'.$filename;
         
        $full_path = $target_dir.$filename;
        $part = $part-1;
        
        if(!is_file($source_dir.'parts/part'.$part.'.txt')){
            return self::translitDOCXFinish($filename, $source_dir, $target_dir, $toVariant, $part-1);
        } else {
            $message = file_get_contents($source_dir.'parts/part'.$part.'.txt');
            $message = self::translitText($message, $toVariant);
            file_put_contents($source_dir.'parts/part'.$part.'.txt', $message);
            return false;
        }
        
    }
    
    private static function translitText($message, $toVariant){
        set_time_limit(300);
        ini_set('memory_limit', '-1');
        require_once('translit/TranslitProcessor.php');
        $TranslitProcessor = new TranslitProcessor();
       
        $message = htmlspecialchars($message);
        $regex = "/(?<=&gt;)([a-zığüşöçñâA-ZĞÜŞİÖÇÑÂ0-9\s:а-яґА-ЯёЁієїІ;.,!?–_—\-+=*%―()\"\'«»“” \/\[\]#№§…°„“\^\$<@©­-]+)(?=&lt;\/)/ui";
        preg_match_all($regex, $message, $out);  
        
        
        
        foreach($out[0] as $index => $match){
            $message = preg_replace($regex, '{{ {'.$index.'} {$1} {'.$index.'} }}', $message, 1);
        }  
        foreach($out[0] as $index => $match){
            if(strpos($match, '_+') === false && strpos($match, '_+') === false){
                $translitted = $TranslitProcessor->translate($match, $toVariant); 
            } else {
                $matched = $match;
                if(strpos($match, '_+') !== false){
                    $beginning_text = explode('_+', $matched)[0];
                    $matched = explode('_+', $matched)[1];
                    $beginning_translitted = $TranslitProcessor->translate($beginning_text, $toVariant);
                    $matched = $beginning_translitted.'_+'.$matched;
                }
                if(strpos($matched, '+_') !== false){
                    $ending_text = explode('+_', $matched)[1];
                    $matched = explode('+_', $matched)[0];
                    $ending_translitted = $TranslitProcessor->translate($ending_text, $toVariant);
                    $matched = $matched.'+_'.$ending_translitted;
                }
                $translitted = $matched;
                $matched = '';
            }
                
            $message = preg_replace('/\{\{ \{'.$index.'\} \{.*\} \{'.$index.'\} \}\}/ui', $translitted, $message, 1);
        }
        return htmlspecialchars_decode($message);
    }
    
    private static function translitDOCXFinish($filename, $source_dir, $target_dir, $toVariant, $total_parts) {
        set_time_limit(300);
        ini_set('memory_limit', '-1');
        require_once('translit/TranslitProcessor.php');
        $TranslitProcessor = new TranslitProcessor();
        $template_file_name = $source_dir.'/'.$filename;
         
        $full_path = $target_dir.$filename;
        
        try
        {    
            //Copy the Template file to the Result Directory
            copy($template_file_name, $full_path);
         
            // add calss Zip Archive
            $zip_val = new ZipArchive;
            //Docx file is nothing but a zip file. Open this Zip File
            if($zip_val->open($full_path) == true)
            {
                // In the Open XML Wordprocessing format content is stored.
                // In the document.xml file located in the word directory.
            
            
                for( $i = 0; $i < $zip_val->numFiles; $i++ ){ 
                    $key_file_name =  $zip_val->getNameIndex($i);
                    $file_extension = array_reverse(explode('.', $key_file_name))[0];
                    if($key_file_name == 'word/document.xml'){
                        $message = '';
                        for($k = 0; $k <= $total_parts; $k++){
                            $message .= file_get_contents($source_dir.'parts/part'.$k.'.txt');
                            if($k != $total_parts){
                                $message .= '</w:p>';
                            }
                        }
                    } else {
                        $message = $zip_val->getFromName($key_file_name);
                        if($file_extension == 'xml'){
                            $message = self::translitText($message, $toVariant);
                        }
                    }
                    //Replace the content with the new content created above.
                    $zip_val->addFromString($key_file_name, $message);
                }
                $zip_val->close();
                return $filename;
            }
        }
        catch (Exception $exc) 
        {
            $error_message =  "Error creating the Word Document";
            var_dump($exc);
        }
    }
    
    private static function explodeDOCX($filename, $source_dir) {
        set_time_limit(300);
        ini_set('memory_limit', '-1');
        
        $template_file_name = $source_dir.'/'.$filename;
         
        $full_path = $source_dir.$filename;
         
        try
        {   
            //Copy the Template file to the Result Directory
            copy($template_file_name, $full_path);
         
            // add calss Zip Archive
            $zip_val = new ZipArchive;
            //Docx file is nothing but a zip file. Open this Zip File
            if($zip_val->open($full_path) == true)
            {
                // In the Open XML Wordprocessing format content is stored.
                // In the document.xml file located in the word directory.
            
                $raw_text = $zip_val->getFromName('word/document.xml');
                $delimiter = '</w:p>';
                $parts = [];
                $chunks = array_chunk(explode($delimiter, $raw_text), 100);
                foreach($chunks as $chunk){
                    $parts[] = implode($delimiter, $chunk);
                }
                
                foreach($parts as $index => $part){
                    file_put_contents($source_dir.'parts/part'.$index.'.txt', $part); 
                }
                $zip_val->close();
                return count($parts);
            }
        }
        catch (Exception $exc) 
        {
            $error_message =  "Error creating the Word Document";
            var_dump($exc);
        }
    }
    
    private static function translitTXT($filename, $source_dir, $target_dir, $toVariant){
        require_once('translit/TranslitProcessor.php');
        $TranslitProcessor = new TranslitProcessor();
        $fileData = file_get_contents($source_dir.$filename);
        $translited_text = $TranslitProcessor->translate($fileData, $toVariant);
        file_put_contents($target_dir.$filename, $translited_text);
        return $filename;
    }
    
    private static function convertToDOCX($filename, $source_dir, $extension){
        if($extension == 'rtf'){
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($source_dir.$filename, 'RTF');
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($source_dir.$filename.'.docx');
        return $source_dir.$filename.'.docx';
    }
    
    private static function makeZip($filelist, $dir, $hash){
        $zip = new ZipArchive();

        $DelFilePath=$hash.".zip";
        
        if(file_exists($dir.$DelFilePath)) {
            unlink ($dir.$DelFilePath); 
        }
        if ($zip->open($dir.$DelFilePath, ZIPARCHIVE::CREATE) != TRUE) {
                die ("Could not open archive");
        }
        foreach($filelist as $filename){
            $zip->addFile($dir.$filename, $filename);
        }
        
        
        // close and save archive
         
        return $zip->close();
    }
    
    
    public static function uploadFilesAjax(){
        $error = false;
        $hash = time();
        $path = "/media/docs/".$hash."/src/";
        $target_dir = JPATH_BASE.$path;
        self::garbageCollect();
        
        foreach($_FILES['file']['name'] as $file_index => $file_name){
            $target_file = $target_dir . $file_name;
            // Check if image file is a actual image or fake image
            // Check width and height
            // Check if file already exists
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            if(!is_dir(JPATH_BASE."/media/docs/".$hash."/trg/")){
                mkdir(JPATH_BASE."/media/docs/".$hash."/trg/", 0777, true);
            }
            if(!is_dir(JPATH_BASE."/media/docs/".$hash."/src/parts/")){
                mkdir(JPATH_BASE."/media/docs/".$hash."/src/parts/", 0777, true);
            }
            
            $extensions = ['docx','txt'];
            foreach($extensions as $extension){
                if(file_exists($target_dir . $file_name . '.' . $extension)){
                    unlink($target_dir . $file_name . '.' . $extension);
                }
            }
            if ( !move_uploaded_file( $_FILES['file']['tmp_name'][$file_index], $target_file ) ) {
                return false;
            }
        }
        return $hash;
    } 
    private static function garbageCollect(){
        $dir_list = scandir(JPATH_BASE."/media/docs/");
        
        foreach($dir_list as $dir_name){
            if($dir_name == '.' || $dir_name == '..'){
                continue;
            }
            if((time() - $dir_name) > 3600){
                self::rrmdir(JPATH_BASE."/media/docs/".$dir_name);
            }
        }
        
    }
    private static function rrmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    self::rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }
    
}
