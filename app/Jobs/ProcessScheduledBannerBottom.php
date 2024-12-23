<?php

namespace App\Jobs;

use App\Models\Common\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledBannerBottom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The setting instance
     *
     * @var Setting
     */
    public Setting $setting;

    /**
     * Create a new job instance.
     *
     * @param Setting $setting
     */
    public function __construct(Setting $setting)
    {
      $this->setting = $setting;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      if($this->setting->next_banner_bottom_enabled) {
        // Replace actual banner to scheduled banner.
        $this->setting->default_banner_bottom_link = $this->setting->next_banner_bottom_link;
        $this->setting->default_banner_bottom = $this->setting->next_banner_bottom_image;

        // Delete scheduled banner.
        $this->setting->next_banner_bottom_enabled = false;
        $this->setting->next_banner_bottom_link = null;
        $this->setting->next_banner_bottom_image = null;
        $this->setting->next_banner_bottom_start_date = null;
        $this->setting->save();
      }
    }
}
