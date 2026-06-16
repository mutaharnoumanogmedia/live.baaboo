<?php

namespace App\Console\Commands;

use App\Services\ActiveCampaign\ActiveCampaignClient;
use Illuminate\Console\Command;
use Throwable;

class TagGameshowAttendees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activecampaign:tag-gameshow-attendees
                            {--source=gameshow_attended : Source tag slug identifying past attendees}
                            {--target=gameshow_attended_general : Tag slug to apply}
                            {--dry-run : Preview without writing to ActiveCampaign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply the gameshow_attended_general tag to every contact who attended in the past.';

    /**
     * Execute the console command.
     */
    public function handle(ActiveCampaignClient $ac): int
    {
        $sourceId = $ac->tagId($this->option('source'));
        $targetId = $ac->tagId($this->option('target'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[dry-run] ' : '')."Tagging contacts with tag #{$sourceId} → #{$targetId}");

        $tagged = 0;
        $failed = 0;

        foreach ($ac->contactsByTag($sourceId) as $contact) {
            $id = (int) ($contact['id'] ?? 0);
            $email = $contact['email'] ?? '(no email)';

            if ($id === 0) {
                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] would tag #{$id} {$email}");
                $tagged++;

                continue;
            }

            try {
                $ac->addTag($id, $targetId);
                $this->line("tagged #{$id} {$email}");
                $tagged++;
                usleep(220_000); // ~4.5 req/s, stay under ActiveCampaign's 5 req/s limit
            } catch (Throwable $e) {
                $this->error("failed #{$id} {$email}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would tag ' : 'Tagged ')."{$tagged} contacts.".($failed ? " {$failed} failed." : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
