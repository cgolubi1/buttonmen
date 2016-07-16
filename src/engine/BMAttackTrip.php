<?php
/**
 * BMAttackTrip: Code specific to trip attacks
 *
 * @author james
 */

/**
 * This class contains code specific to trip attacks
 */
class BMAttackTrip extends BMAttack {
    /**
     * Type of attack
     *
     * @var string
     */
    public $type = 'Trip';

    /**
     * Determine if there is at least one valid attack of this type from
     * the set of all possible attackers and defenders.
     *
     * If $includeOptional is FALSE, then optional attacks are excluded.
     * These include skill attacks involving warrior dice.
     *
     * @param BMGame $game
     * @param bool $includeOptional
     * @return bool
     */
    public function find_attack($game, $includeOptional = TRUE) {
        $targets = $game->defenderAllDieArray;

        return $this->search_onevone($game, $this->validDice, $targets);
    }

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function validate_attack($game, array $attackers, array $defenders) {
        $this->validationMessage = '';

        if (count($attackers) != 1) {
            $this->validationMessage = 'There must be exactly one attacking die for a trip attack.';
            return FALSE;
        }

        if (count($defenders) != 1) {
            $this->validationMessage = 'There must be exactly one target die for a trip attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
            return FALSE;
        }

        if ($this->is_disabled_by_konstant($attackers, $defenders)) {
            // validation message set within $this->is_disabled_by_konstant()
            return FALSE;
        }

        if ($this->is_disabled_by_maximum($attackers, $defenders)) {
            // validation message set within $this->is_disabled_by_maximum()
            return FALSE;
        }

        $attacker = $attackers[0];
        $defender = $defenders[0];

        $isDieLargeEnough = ($attacker->max >= $defender->min);

        return $isDieLargeEnough;
    }

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attArray must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot perform trip attacks.';
            return FALSE;
        }

        if ($att->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot perform trip attacks.';
            return FALSE;
        }

        if (!$att->has_skill('Trip')) {
            $this->validationMessage = 'Dice without trip cannot perform trip attacks.';
            return FALSE;
        }

        if ($def->has_skill('Stealth')) {
            $this->validationMessage = 'Stealth dice cannot be the target of trip attacks.';
            return FALSE;
        }

        if ($def->has_skill('Warrior')) {
            $this->validationMessage = 'Warrior dice cannot be attacked.';
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check whether the trip attack is disabled by the konstant die skill
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function is_disabled_by_konstant($attArray, $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attack must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Konstant') && ($att->value < $def->min)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        if ($att->has_skill('Konstant')) {
            $attMaxVal = $att->value;
        } else {
            $attMaxVal = self::post_trip_roll_max($att);
        }

        if ($def->has_skill('Konstant') && ($attMaxVal < $def->value)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        if ($att->has_skill('Konstant') &&
            $def->has_skill('Konstant') &&
            ($att->value < $def->value)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Check whether the trip attack is disabled by the maximum die skill
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    protected function is_disabled_by_maximum($attArray, $defArray) {
        if (1 != count($attArray)) {
            throw new InvalidArgumentException('attack must have one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $att = $attArray[0];
        $def = $defArray[0];

        if ($att->has_skill('Konstant')) {
            $attMaxVal = $att->value;
        } else {
            $attMaxVal = self::post_trip_roll_max($att);
        }

        $defMaxVal = self::post_trip_roll_max($def);

        if (is_null($attMaxVal) || is_null($defMaxVal)) {
            return FALSE;
        }

        if ($def->has_skill('Maximum') && ($attMaxVal < $defMaxVal)) {
            $this->validationMessage = 'The attacking die cannot roll high enough to capture the target die';
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Calculates the die max after rolling a die because of a trip attack
     *
     * @param BMDie $die
     * @return int
     */
    public static function post_trip_roll_max($die) {
        $clone = clone $die;
        $postRollMax = $clone->max;

        if ($clone->has_skill('Weak')) {
            $clone->shrink();
            $postRollMax = $clone->max;
        }

        if ($clone->has_skill('Mighty')) {
            $clone->grow();
            $postRollMax = $clone->max;
        }

        if ($clone->has_skill('Mood') || $clone->has_skill('Mad')) {
            $postRollMax = NULL;
        }

        return $postRollMax;
    }
}
