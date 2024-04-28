<?php
try {
  $db = new PDO('sqlite:database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Get all players whose status is 'yes' from the messages table
  $selectPlayers = $db->prepare("SELECT * FROM messages WHERE status = 'yes'");
  $selectPlayers->execute();
  $players = $selectPlayers->fetchAll(PDO::FETCH_ASSOC);

  // Get the community cards from the cards table
  $selectCards = $db->query("SELECT * FROM cards");
  $communityCards = $selectCards->fetchAll(PDO::FETCH_ASSOC);

  // Prepare the players' hands
  $hands = [];
  foreach ($players as $player) {
    // Each player's hand consists of their own cards plus the community cards
    $hand = [
      ['number' => $player['cardNumber1'], 'suit' => $player['suit1']],
      ['number' => $player['cardNumber2'], 'suit' => $player['suit2']],
    ];
    foreach ($communityCards as $card) {
      $hand[] = ['number' => $card['cardNumber'], 'suit' => $card['suit']];
    }
    $hands[] = $hand;
  }
  $counts = 0;
  foreach ($hands as $hand) {
    // Get all combinations of 5 cards
    $combinations = combinations($hand, 5);

    // Initialize the score for the hand
    $score = 0;

    foreach ($combinations as $combination) {
      // Sort the cards by number
      usort($combination, function ($a, $b) {
        return $a['number'] - $b['number'];
      });

      // Check if the combination is a royal flush
      if (isRoyalFlush($combination)) {
        $score = max($score, 9);
        continue;
      }

      // Check if the combination is a straight flush
      if (isStraightFlush($combination)) {
        $highCard = getHighCardForStraight($combination); // Ace is counted as 14 for 10-J-Q-K-A, and 5 for A-2-3-4-5
        $score = max($score, 8 + $highCard * 0.01);
        continue;
      }

      // Check if the combination is four of a kind (quads)
      if (isFourOfAKind($combination)) {
        $quadCard = getQuadCard($combination);
        $fifthCard = getFifthCard($combination, $quadCard);
        $score = max($score, 7 + $quadCard * 0.01 + $fifthCard * 0.0001);
        continue;
      }

      // Check if the combination is a full house
      if (isFullHouse($combination)) {
        $tripleCard = getTripleCard($combination);
        $pairCard = getPairCard($combination);
        if($tripleCard == 1){
          $tripleCard = 14;
        }
        if($pairCard == 1){
          $pairCard = 14;
        }
        $score = max($score, 6 + $tripleCard * 0.01 + $pairCard * 0.0001);
        continue;
      }

      // Check if the combination is a flush
      if (isFlush($combination)) {
        $score = max($score, 5 + calculateCardScore($combination));
        continue;
      }

      // Check if the combination is a straight
      if (isStraight($combination)) {
        $highCard = getHighCardForStraight($combination); // Ace is counted as 14 for 10-J-Q-K-A, and 5 for A-2-3-4-5
        $score = max($score, 4 + $highCard * 0.01);
        continue;
      }

      // Check if the combination is three of a kind
      if (isThreeOfAKind($combination)) {
        $tripleCard = getTripleCard($combination);
        $remainingCards = getRemainingCards($combination, $tripleCard);
        if($tripleCard == 1){
          $tripleCard = 14;
        }
        $score = max($score, 3 + $tripleCard * 0.01 + calculateCardScore($remainingCards));
        continue;
      }

      // Check if the combination is two pair
      if (isTwoPair($combination)) {
        $highPair = getHighPair($combination);
        $lowPair = getLowPair($combination);
        $fifthCard = getFifthCard($combination, $highPair, $lowPair);
        if($lowPair == 1){
          $lowPair = 14;
        }
        if($lowPair>$highPair){
          $lowPair = $highPair;
          $highPair = 14;
        }
        $score = max($score, 2 + $highPair * 0.01 + $lowPair * 0.0001 + $fifthCard * 0.000001);
        continue;
      }

      // Check if the combination is a pair
      if (isPair($combination)) {
        $pairCard = getPairCard($combination);
        $remainingCards = getRemainingCards($combination, $pairCard);
        if($pairCard == 1){
          $pairCard = 14;
        }
        $score = max($score, 1 + $pairCard * 0.01 + calculateCardScore($remainingCards));
        continue;
      }

      // If no other hand is found, it's a high card hand
      $score = max($score, calculateCardScore($combination));
    }
    $insertWinner = $db->prepare("INSERT INTO winners (name, score) VALUES (:name, :score)");
    $insertWinner->bindValue(':name', $players[$counts]['name']);
    $insertWinner->bindValue(':score', number_format($score, 12));
    $insertWinner->execute();
    $counts = $counts + 1;
  }
} catch (Exception $e) {
  echo "Failed: " . $e->getMessage();
}

  // Function to get all combinations of $n elements from the array $arr
  function combinations($arr, $n) {
    $result = [];
    $combinations = array_combinations(count($arr), $n);
    foreach ($combinations as $combination) {
      $result[] = array_intersect_key($arr, array_flip($combination));
    }
    return $result;
  }

  // Function to get all combinations of $n elements from the numbers 0 to $m-1
  function array_combinations($m, $n) {
    if ($n == 0) {
      return [[]];
    }
    if ($m == 0) {
      return [];
    }
    $combinations = array_combinations($m - 1, $n);
    foreach (array_combinations($m - 1, $n - 1) as $combination) {
      $combination[] = $m - 1;
      $combinations[] = $combination;
    }
    return $combinations;
  }

  // Functions to check for various poker hands
  function isRoyalFlush($hand) {
    return $hand[0]['number'] == 1 && // Ace
           $hand[1]['number'] == 10 && // 10
           $hand[2]['number'] == 11 && // Jack
           $hand[3]['number'] == 12 && // Queen
           $hand[4]['number'] == 13 && // King
           count(array_unique(array_column($hand, 'suit'))) == 1; // All the same suit
  }

  function isStraightFlush($hand) {
    return isStraight($hand) && isFlush($hand);
  }

  function isFourOfAKind($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return in_array(4, $counts);
  }

  function isFullHouse($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return in_array(3, $counts) && in_array(2, $counts);
  }

  function isFlush($hand) {
    return count(array_unique(array_column($hand, 'suit'))) == 1;
  }

  function isStraight($hand) {
    $numbers = array_column($hand, 'number');
    sort($numbers);
    if ($numbers == [1, 2, 3, 4, 5] || $numbers == [1, 10, 11, 12, 13]) {
      return true;
    }
    for ($i = 0; $i < count($numbers) - 1; $i++) {
      if ($numbers[$i] + 1 != $numbers[$i + 1]) {
        return false;
      }
    }
    return true;
  }

  function isThreeOfAKind($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return in_array(3, $counts);
  }

  function isTwoPair($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return count(array_filter($counts, function ($x) { return $x == 2; })) == 2;
  }

  function isPair($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return in_array(2, $counts);
  }

  // Functions to get the card of the four of a kind, the triple card, the pair card, and the fifth card
  function getQuadCard($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return array_search(4, $counts);
  }

  function getTripleCard($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return array_search(3, $counts);
  }

  function getPairCard($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    return array_search(2, $counts);
  }

  function getHighCard($hand, $excludeCard) {
    $highCard = 0;
    foreach ($hand as $card) {
      if ($card['number'] != $excludeCard && $card['number'] > $highCard) {
        $highCard = $card['number'];
      }
    }
    return $highCard;
  }

  function getHighPair($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    $pairs = array_keys($counts, 2);
    return max($pairs);
  }

  function getLowPair($hand) {
    $counts = array_count_values(array_column($hand, 'number'));
    $pairs = array_keys($counts, 2);
    return min($pairs);
  }
  function getFifthCard($hand, $highPair, $lowPair) {
    foreach ($hand as $card) {
      if ($card['number'] != $highPair && $card['number'] != $lowPair) {
        return $card['number'];
      }
    }
  }
  // Function to calculate the score for a hand of cards
  function calculateCardScore($hand) {
    $score = 0;
    usort($hand, function($a, $b) {
        if ($a['number'] == 1) {
            return -1;
        } elseif ($b['number'] == 1) {
            return 1;
        } else {
            return $b['number'] - $a['number']; // Sort in descending order based on 'number' value
        }
    });
    $i=1;
    foreach ($hand as $card) {

      $cardValue = $card['number'] == 1 ? 14 : $card['number']; // Ace is counted as 14
      $score += 0.01*($cardValue * pow(0.01, $i));
      $i = $i+1;
    }
    return $score;
  }
  // Function to get the remaining cards in the hand after excluding a specific card
  function getRemainingCards($hand, $excludeCard) {
    $remainingCards = [];
    foreach ($hand as $card) {
      if ($card['number'] != $excludeCard) {
        $remainingCards[] = $card;
      }
    }
    return $remainingCards;
  }
  function getHighCardForStraight($hand) {
    $numbers = array_column($hand, 'number');
    if (in_array(1, $numbers)) {
      return in_array(2, $numbers) ? 5 : 14; // Ace is counted as 14 for 10-J-Q-K-A, and 5 for A-2-3-4-5
    }
    return max($numbers);
  }
?>