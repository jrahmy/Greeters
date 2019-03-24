<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Greeters\XF\Service\User;

/**
 * Extends \XF\Service\User\Registration
 */
class Registration extends XFCP_Registration
{
    /**
     * Contacts users when a registration is completed.
     */
    protected function sendRegistrationContact()
    {
        parent::sendRegistrationContact();

        if ($this->user->user_state == 'valid') {
            /** @var \Jrahmy\Greeters\Repository\Greeter $greeterRepo */
            $greeterRepo = $this->repository('Jrahmy\Greeters:Greeter');
            $greeters = $greeterRepo->getGreetersForNotifier();

            if ($greeters) {
                /** @var \Jrahmy\Greeters\Service\User\Notifier $notifier */
                $notifier = $this->service(
                    'Jrahmy\Greeters:User\Notifier',
                    $this->user
                );
                $notifier->setNotifyJoined($greeters);
                $notifier->notify();
            }
        }
    }
}
