<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Request;

class SaveContactRequest extends Request
{
    const PROPERTIES = [
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'message' => 'string',
        'locale' => 'string',
        'preferences' => 'SweepBright\Request\SaveContactRequestPreferences',
        'location_preference' => 'SweepBright\Request\SaveContactRequestLocationPreference',
    ];
}
