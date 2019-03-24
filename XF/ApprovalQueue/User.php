<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Greeters\XF\ApprovalQueue;

/**
 * Extends \XF\ApprovalQueue\User
 */
class User extends XFCP_User
{
    /**
     * @param \XF\Entity\User $user
     */
    public function actionApprove(\XF\Entity\User $user)
    {
        parent::actionApprove($user);

        /** @var \Jrahmy\Greeters\Repository\Greeter $greeterRepo */
        $greeterRepo = \XF::repository('Jrahmy\Greeters:Greeter');
        $greeters = $greeterRepo->getGreetersForNotifier();

        if ($greeters) {
            /** @var \Jrahmy\Greeters\Service\User\Notifier $notifier */
            $notifier = \XF::app()->service(
                'Jrahmy\Greeters:User\Notifier',
                $user
            );
            $notifier->setNotifyJoined($greeters);
            $notifier->notify();
        }
    }
}
