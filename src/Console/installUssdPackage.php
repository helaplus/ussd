<?php

namespace Helaplus\Ussd\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallUssdPackage extends Command
{
    protected $signature = 'Ussd:install';

    protected $description = "Install Ussd Package";

    public function handle(){
        $this->info('Installing UssdPackage...');

        $this->info('Publishing configuration');

        if(! $this->configExists('ussd.php')){
            $this->publishConfiguration();
            $this->info('Published configuration');
        }else{
            if($this->shouldOverwriteConfig()){
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else{
                $this->info('Existing configuration was not overwritten');
            }
        }

        $this->info ('Installed UssdPackage');
    }

    private function configExists($fileName){
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig(){
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?', false
        );
    }

    private function publishConfiguration($forcePublish = false){
        $params = [
          '--provider' => 'Helaplus\Ussd\UssdServiceProvider.php',
            '--tag' => "config"
        ];

        if($forcePublish === true){
            $params['--force'] = true;
        }

        $this->call('vendor:publish',$params);
    }
}