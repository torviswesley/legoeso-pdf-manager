<?php
namespace Legoeso_PDF_Manager\Inc\Common;

use \ErrorException;

class PDM_Exception_Error extends ErrorException {
    private string $errorFile;
    private int $errorLine;
    
    public function setErrorFile($errorFile): PDM_Exception_Error { $this->errorFile = $errorFile; return $this; }
    public function setErrorLine($errorLine): PDM_Exception_Error { $this->errorLine = $errorLine; return $this; }
    public function getErrorFile(): string { return $this->errorFile; }
    public function getErrorLine(): int { return $this->errorLine; }
    public function getErrorObject($_exception): array { 
        return array(
        'error_message' =>  $_exception->getMessage(),
        'error_code'    =>  $_exception->getCode(),
        'error_line'    =>  $_exception->getLine(),
        'filename'      =>  $_exception->getFile(),
        );
    } 
}
?>