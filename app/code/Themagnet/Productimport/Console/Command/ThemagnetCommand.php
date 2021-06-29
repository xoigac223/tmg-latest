<?php
namespace Themagnet\Productimport\Console\Command;

class ThemagnetCommand
{
    public function isCommandExecte($output , $_csvfiles)
    {
        if($_csvfiles->isLokFileExists('simple') !== true){
            $output->writeln('<error>You need to import simple product first.</error>');
            return false;
        }
    }

    public function isErrorMessage($output , $message)
    {
        $output->writeln('<error>'.$message.'</error>');
        return false;
    }
}