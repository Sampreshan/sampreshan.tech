<?php
/** Shared bank audit for CLI, CI and restricted editorial review. No bank text is public. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function sp_quiz_normalize_text( $text ) {
    $text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    if ( class_exists( 'Normalizer' ) ) { $text = Normalizer::normalize( $text, Normalizer::FORM_KC ); }
    $text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
    $text = preg_replace( '/[^\p{L}\p{N}\s]+/u', ' ', $text );
    return trim( preg_replace( '/\s+/u', ' ', $text ) );
}

function sp_quiz_option_info( $label ) {
    static $terms = null;
    if ( null === $terms ) {
        $terms = array();
        foreach ( require __DIR__ . '/glossary.php' as $term => $meaning ) {
            $terms[sp_quiz_normalize_text( $term )] = $meaning;
        }
    }
    return $terms[sp_quiz_normalize_text( $label )] ?? '';
}

/** Flat cards retain stable IDs; do not remove/reindex cards used by saved journeys. */
function sp_quiz_audit_cards( array $cards ) {
    $report = array( 'cards' => count( $cards ), 'duplicateQuestions' => array(), 'duplicateCards' => array(), 'nearDuplicates' => array(), 'missingMeanings' => array() );
    $questions = $signatures = $previous = array();
    foreach ( $cards as $id => $fields ) {
        $question = sp_quiz_normalize_text( $fields[0] );
        $options = array_map( 'sp_quiz_normalize_text', array_slice( $fields, 1 ) ); sort( $options );
        $signature = $question . '||' . implode( '|', $options );
        $pair = array( 'second' => $id, 'question' => $fields[0] );
        if ( isset( $questions[$question] ) ) { $report['duplicateQuestions'][] = array_merge( array( 'first' => $questions[$question] ), $pair ); }
        if ( isset( $signatures[$signature] ) ) { $report['duplicateCards'][] = array_merge( array( 'first' => $signatures[$signature] ), $pair ); }
        $questions[$question] = $questions[$question] ?? $id;
        $signatures[$signature] = $signatures[$signature] ?? $id;
        // Token overlap catches reordered wording. Shared options also flag paraphrase candidates.
        // This is an editorial review heuristic, not a semantic-equivalence guarantee.
        $tokens = array_values( array_unique( array_diff( explode( ' ', $question ), array( 'a', 'an', 'the', 'is', 'of', 'in', 'which', 'what', 'who', 'does', 'are', 'as', 'to' ) ) ) );
        foreach ( $previous as $other => $prior ) {
            if ( $question === $prior['question'] ) { continue; }
            $union = count( array_unique( array_merge( $tokens, $prior['tokens'] ) ) );
            $score = $union ? count( array_intersect( $tokens, $prior['tokens'] ) ) / $union : 0;
            $shared = count( array_intersect( $options, $prior['options'] ) );
            if ( $score >= 0.8 || ( $score >= 0.5 && $shared >= 3 ) ) {
                $report['nearDuplicates'][] = array( 'first' => $other, 'second' => $id, 'similarity' => round( $score, 3 ), 'question' => $fields[0] );
            }
        }
        $previous[$id] = array( 'question' => $question, 'tokens' => $tokens, 'options' => $options );
        foreach ( array_slice( $fields, 1 ) as $label ) {
            if ( '' === sp_quiz_option_info( $label ) ) {
                $key = sp_quiz_normalize_text( $label );
                if ( ! isset( $report['missingMeanings'][$key] ) ) { $report['missingMeanings'][$key] = array( 'label' => $label, 'cards' => array() ); }
                $report['missingMeanings'][$key]['cards'][] = $id;
            }
        }
    }
    $report['missingMeanings'] = array_values( $report['missingMeanings'] );
    return $report;
}

function sp_quiz_bank_cards( array $bank ) {
    $cards = array();
    foreach ( $bank as $level => $data ) {
        foreach ( $data['items'] as $i => $fields ) { $cards[sprintf( 'L%02d-Q%03d', $level, $i + 1 )] = $fields; }
    }
    return $cards;
}
