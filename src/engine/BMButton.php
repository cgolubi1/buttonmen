<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BMButton
 *
 * @author james
 */
class BMButton {
    // properties
    public $recipe;
    public $dieArray;
    // three lists of dice

    // methods
    public function loadFromRecipe($recipe) {
        $this->validateRecipe($recipe);
        $dieSides = $this->parseRecipeForSides($recipe);
        $dieSkills = $this->parseRecipeForSkills($recipe);
        unset($this->dieArray);

        // set die sides and skills, one die at a time
        for ($dieIdx = 0; $dieIdx <= (count($dieSides) - 1); $dieIdx++) {
            $tempBMDie = new BMDie;
            $tempBMDie->mSides = $dieSides[$dieIdx];
            if (!empty($dieSkills[$dieIdx])) {
                $tempBMDie->mSkills = $dieSkills[$dieIdx];
            }
            $this->dieArray[] = $tempBMDie;
        }
    }

    private function validateRecipe($recipe) {
        $dieArray = preg_split('/[[:space:]]+/', $recipe,
                               NULL, PREG_SPLIT_NO_EMPTY);

        for ($dieIdx = 0; $dieIdx < count($dieArray); $dieIdx++) {
            $dieContainsDigit = preg_match('/[[:digit:]]/', $dieArray[$dieIdx]);
            print($dieContainsDigit);
            if (1 !== $dieContainsDigit) {
                throw new InvalidArgumentException("Invalid button recipe.");
            }
        }
    }

    private function parseRecipeForSides($recipe) {
        $dieSizeArray = preg_split('/[^[:digit:]]+/', $recipe,
                                   NULL, PREG_SPLIT_NO_EMPTY);
        return $dieSizeArray;
    }

    private function parseRecipeForSkills($recipe) {
        $dieSkillArray = preg_split('/[[:digit:][:space:]]+/', $recipe);
        return $dieSkillArray;
    }

    // create dice

    // load die values
}

?>
