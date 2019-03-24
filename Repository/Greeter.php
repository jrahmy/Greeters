<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\Greeters\Repository;

use XF\Mvc\Entity\Repository;

/**
 * A repository for greeters.
 */
class Greeter extends Repository
{
    /**
     * @return array
     */
    public function getGreetersForNotifier()
    {
        $options = $this->options();

        $userGroup = $options->jGreetersUserGroup;
        if (!$userGroup) {
            return [];
        }

        /** @var \XF\Searcher\User $searcher */
        $searcher = $this->app()->searcher('XF:User', [
            'secondary_group_ids' => $userGroup
        ]);
        $finder = $searcher->getFinder()
            ->isValidUser()
            ->order('last_activity', 'desc');

        $activityCutOff = $options->jGreetersActivityCutOff;
        if ($activityCutOff) {
            $finder->isRecentlyActive($activityCutOff);
        }

        $maxUsers = $options->jGreetersMaxUsers ?: null;
        /** @var \XF\Mvc\Entity\AbstractCollection $users */
        $users = $finder->fetch($maxUsers);
        return $users->pluckNamed('user_id');
    }
}
