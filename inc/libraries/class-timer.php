<?php 
/**
 *
 * @link       http://www.legoeso.com
 * @since      1.0.1
 * 
 * @author     Torvis Wesley
 */
class Timer
{
    private $startTime = null;
    private $strTime = null;

    public function __construct($showSeconds = true){
        $this->startTime = microtime(true);
        $sf_time = sprintf('%.4f',$this->startTime);
        $raw_time = DateTime::createFromFormat('U.u',$sf_time);
        $this->strTime = $raw_time->format('H:i:s.u');
    }

    public function startTimer()
    {
        return( $this->strTime );
    }

    public function stopTimer()
    {
        $endTime = microtime(true);
        $time = $endTime - $this->startTime;

        $sf_time = sprintf('%.4f',$time);
        $raw_time = DateTime::createFromFormat('U.u',$sf_time);
        return( $raw_time->format('H:i:s.u') ) ;

    }
}


?>