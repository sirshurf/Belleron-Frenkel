<?php
class Bf_Plugin_Cron_SendMail extends Bf_Plugin_Cron_CronAbstract
{

    public function run()
    {
             $strNewMailMessage = "New Servers: "."<br/>" . PHP_EOL;
          
            
            try {
                $objMail = new Zend_Mail();
                $objMail->setBodyHtml($strNewMailMessage);
                $objMail->addTo("omert@mellanox.com");
                $objMail->addCc("sirshurf@gmail.com");
                
                $objMail->setSubject("Oracle Importer New Servers");
                $objMail->send();
            } catch (Exception $e) {
                Zend_Debug::dump($e);
            }
    }
    
    
}