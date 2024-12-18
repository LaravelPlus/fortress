<?php

declare(strict_types=1);

namespace Laravelplus\Fortress\Commands;

use Illuminate\Console\Command;

final class InstallFortressCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortress:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Fortress package and publish its configuration.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Fortress package...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'config',
            '--force' => true,
        ]);

        $this->info('Fortress has been successfully installed.');

        return self::SUCCESS;
    }
}
