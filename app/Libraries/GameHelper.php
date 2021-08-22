<?php

namespace App\Libraries;

use \App\Models\Player;
use \App\Models\DiceCup;

class GameHelper
{
    public function initializeGame()
    {
        $players = $this->createPlayers([
            'Player One',
            'Player Two',
            'Player Three',
            'Player Four',
        ]);

        $roundNumber = 1;
        $hasWinner = false;

        while (!$hasWinner) {
            $roundScoreSheet = [];

            # Run through all the players and get
            # their score for current round
            foreach ($players as $playerPosition => $player) {   
                $playerRoundScore = $this->playRound($player, $players, $playerPosition);
                $roundScoreSheet = $this->addScoreInRoundScoreSheet(
                    $playerRoundScore, 
                    $roundScoreSheet, 
                    $player
                );
            }

            # Display the Score after dice rolled
            echo "ROUND $roundNumber\n\n";
            echo "After Dice Rolled:\n";
            $this->displayRoundScore($roundScoreSheet);
        
            # Pass all the dice to the players beside
            $this->passDiceAcrossPlayers($players); 

            # Setup the Score after the dice moved/removed
            $diceMovedRoundScore = [];
            $diceMovedRoundScore = $this->populateMovedRoundScore(
                $players
            ); 

            # Display the Score after dice moved/removed
            echo "After Dice Moved/Removed: \n";
            $this->displayRoundScore($diceMovedRoundScore);

            # Check if there is a valid winner
            $hasWinner = $this->checkIfHaveWinner($players);
           
            $roundNumber++;         
        }
    }

    public function createPlayers(array $names)
    {   
        $players = collect([]);

        foreach ($names as $name) {
            $player = new Player($name, new DiceCup(6));
            $players->push($player);
        }

        return $players;
    }

    public function checkIfHaveWinner($players)
    {
        $hasWinner = false;
        foreach ($players as $player) {
            $cup = $player->getDiceCup();
            if ($cup->isEmptyCup()) {
                $hasWinner = true;
            }
        }

        return $hasWinner;
    }

    public function playRound(Player $player, $players, $playerPosition)
    {
        $cup = $player->getDiceCup();
        $allDice = $cup->getAllDice();

        $rolledDice = collect([]);
        foreach ($allDice as $dice) {
            $dice->roll();
            $rolledDice->push($dice); 
        }

        $diceShouldBePassed = $this->diceByTopValue($rolledDice, 1);
        $diceCountShouldBeRemoved = $this->diceByTopValue($rolledDice, 6);
        $diceToReturn = $this->diceToReturn(
            $rolledDice
        );

        $diceRollRoundScore = $this->getRolledDiceScores($rolledDice);

        $cup->addMultipleDice($diceToReturn);
        $player->setDiceCup(
            $cup
        );

        $this->passDiceToPlayerToTheRight(
            $players,
            $diceShouldBePassed,
            $playerPosition
        );

        return $diceRollRoundScore;
    }

    public function populateMovedRoundScore($players)
    {
        $roundScoreSheet = [];

        foreach ($players as $player) {
            $cup = $player->getDiceCup();
            $allDice = $cup->peekAtAllDice();

            $playerRoundScore = $this->getRolledDiceScores($allDice);
            $roundScoreSheet = $this->addScoreInRoundScoreSheet(
                $playerRoundScore, 
                $roundScoreSheet, 
                $player
            );
        }

        return $roundScoreSheet;
    }

    public function displayRoundScore($roundScore)
    {
        foreach ($roundScore as $playerName => $scores) {
            echo "$playerName Score: ".implode(',', $scores);
            echo "\n";
        }

        echo "\n";
    }

    public function passDiceAcrossPlayers($players)
    {
        foreach ($players as $player) {
            if ($player->getDiceToAddInCup()->count() != 0) {
                $cup = $player->getDiceCup();
                $cup->addMultipleDice($player->getDiceToAddInCup());   
                $player->setDiceCup(
                    $cup
                ); 
            }
        }
    }

    public function addScoreInRoundScoreSheet($playerRoundScore, $roundScoreSheet, $player)
    {
        if (!isset($roundScoreSheet[$player->name])) {
            $roundScoreSheet[$player->name] = [];
        }

        $roundScoreSheet[$player->name] = $playerRoundScore;
            
        return $roundScoreSheet;
    }

    public function getRolledDiceScores($rolledDice)
    {
        $scores = [];
        foreach ($rolledDice as $dice) {
            $scores[] = $dice->getTopValue();
        }

        return $scores;
    }

    public function passDiceToPlayerToTheRight(
        $players,
        $diceShouldBePassed,
        $currentPlayerPosition
    ) {
        $nextPlayerPosition = $currentPlayerPosition+1;
        $playerToPassDice = $players->get($nextPlayerPosition);

        if (is_null($playerToPassDice)) {
            $playerToPassDice = $players->first();
        }

        $playerToPassDice->setDiceToAddInCup($diceShouldBePassed);
    }

    public function diceByTopValue($rolledDice, $topValue)
    {   
        $filtered = $rolledDice->filter(function ($dice) use ($topValue) {
            return $dice->getTopValue() == $topValue;
        });

        return $filtered;
    }

    public function diceToReturn($rolledDice) 
    {
    	$filtered = $rolledDice->reject(function ($dice) {
            return (in_array($dice->getTopValue(), [1,6]));
        });

        return $filtered;
    }
}
