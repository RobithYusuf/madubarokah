<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixUserEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-emails {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix invalid user emails for Tripay compatibility';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking user emails for Tripay compatibility...');
        $this->line('');

        $users = User::all();
        $invalidUsers = [];
        $validUsers = 0;

        // Check all users
        foreach ($users as $user) {
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $invalidUsers[] = $user;
            } else {
                $validUsers++;
            }
        }

        $this->info("📊 Email Status Summary:");
        $this->line("✅ Valid emails: {$validUsers}");
        $this->line("❌ Invalid emails: " . count($invalidUsers));
        $this->line('');

        if (empty($invalidUsers)) {
            $this->info('🎉 All user emails are valid! No action needed.');
            return self::SUCCESS;
        }

        // Show invalid emails
        $this->warn('Users with invalid emails:');
        $tableData = [];
        foreach ($invalidUsers as $user) {
            $newEmail = 'customer' . $user->id . '@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'example.com'));
            $tableData[] = [
                $user->id,
                $user->name,
                $user->email,
                $newEmail,
                $user->role ?? 'pembeli'
            ];
        }

        $this->table([
            'ID',
            'Name',
            'Current Email',
            'Proposed Email',
            'Role'
        ], $tableData);

        if ($this->option('dry-run')) {
            $this->info('');
            $this->info('🔍 DRY RUN MODE - No changes made');
            $this->info('💡 Run without --dry-run to apply changes');
            return self::SUCCESS;
        }

        $this->line('');
        $confirm = $this->confirm('Do you want to update these email addresses?');

        if (!$confirm) {
            $this->info('❌ Operation cancelled');
            return self::SUCCESS;
        }

        // Update invalid emails
        $updated = 0;
        $failed = 0;

        foreach ($invalidUsers as $user) {
            try {
                $newEmail = 'customer' . $user->id . '@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'example.com'));
                
                $user->update(['email' => $newEmail]);
                $updated++;
                
                $this->line("✅ Updated user {$user->id}: {$user->name} -> {$newEmail}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Failed to update user {$user->id}: " . $e->getMessage());
            }
        }

        $this->line('');
        $this->info("📊 Update Results:");
        $this->info("✅ Successfully updated: {$updated}");
        if ($failed > 0) {
            $this->error("❌ Failed to update: {$failed}");
        }

        $this->line('');
        $this->info('🎉 Email fixing completed!');
        $this->info('💡 Users can now use Tripay payment methods without email validation errors');

        return self::SUCCESS;
    }
}
