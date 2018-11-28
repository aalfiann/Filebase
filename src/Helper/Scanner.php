<?php
namespace Filebase\Helper;

class Scanner {

    /**
     * fileSearch is using opendir (very fast)
     * 
     * @param dir = is the full path of directory
     * @param ext = is the extension of file. Default is php extension.
     * 
     * @return array
     */
    public static function fileSearch($dir, $ext='php') {
        $pattern = "/\\.{$ext}$/";
        $files = [];
        $fh = opendir($dir);

        while (($file = readdir($fh)) !== false) {
            if($file == '.' || $file == '..')
                continue;

            $filepath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filepath))
                $files = array_merge($files, self::fileSearch($filepath, $pattern));
            else {
                if(preg_match($pattern, $file))
                    array_push($files, $filepath);
            }
        }
        closedir($fh);
        return $files;
    }

    /**
     * filesystemIterator
     * 
     * @param dir = is the full path of directory
     * @param ext = is the extension of file. Default is php extension.
     * 
     * @return array RegexIterator
     */
    public static function filesystemIterator($dir,$ext='php'){
        $filesystemIterator = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        return new \RegexIterator($filesystemIterator, "/\\.{$ext}$/");
    }

    /**
     * regexFileIterator
     * 
     * @param dir = is the full path of directory
     * @param ext = is the extension of file. Default is php extension.
     * 
     * @return array RegexIterator
     */
    public static function regexFileIterator($dir,$ext='php'){
        $dirs = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dirs);
        return new \RegexIterator($iterator, "/\\.{$ext}$/");
    }

    /**
     * regexIterator
     * 
     * @param dir = is the full path of directory
     * @param ext = is the extension of file. Default is php extension.
     * 
     * @return array RegexIterator
     */
    public static function regexIterator($dir,$rgx="/\\.{php}$/"){
        $dirs = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dirs);
        return new \RegexIterator($iterator, $rgx);
    }

    /**
     * recursiveCallbackIterator
     * 
     * @param dir = is the full path of directory
     * @param ext = is the extension of file. Default is php extension.
     * 
     * @return array RecursiveIteratorIterator
     */
    public static function recursiveCallbackIterator($dir,$ext='php'){
        $dirs = new \RecursiveDirectoryIterator($dir);
        $filter = new \RecursiveCallbackFilterIterator($dirs, function($current, $key, $iterator) {
            if ($iterator->hasChildren())
                return true;
            if($current->isFile() && preg_match("/\\.{$ext}$/", $current->getFilename() ) )
                return true;
        });
        return new \RecursiveIteratorIterator($filter);
    }

    /**
     * recursiveGlob (support wilcard but very not recommended for big directory with million files)
     * 
     * @param pattern = is the pattern. No tilde expansion or parameter substitution is done.
     * @param flags = is the options for glob.
     * 
     * @return array
     */
    public static function recursiveGlob($pattern, $flags = 0){
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
            $files = array_merge($files, self::recursiveGlob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

}