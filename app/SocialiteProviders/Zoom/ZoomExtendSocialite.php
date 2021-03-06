<?php

namespace App\SocialiteProviders\Zoom;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ZoomExtendSocialite {
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled) {
        $socialiteWasCalled->extendSocialite(
            'zoom', __NAMESPACE__ . '\Provider'
        );
    }
}
