<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Parser for detailed report.
 *
 * @package deffinumreport_detailed
 * @copyright 2025 Edunao SAS (contact@edunao.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace deffinumreport_detailed;

/**
 * Parser & decorator for the detailed_progress JSON coming from VR / RA / 360 modules.
 * Enriches every element/question/choice with helper flags required by the Mustache layer.
 *
 * Usage example:
 *   $json   = file_get_contents($filepath);
 *   $parsed = mod_myplugin_json_parser::parse_from_string($json);
 *   echo $OUTPUT->render_from_template('mod_myplugin/report', $parsed);
 */
final class json_parser {
    /**
     * Allowed element types that can be used in the detailed progress structure.
     *
     * @var string[]
     */
    private const ALLOWED_ELEMENT_TYPES   = ['chapter', 'sequence', 'quiz', 'scenesGroup', 'scene', 'component'];
    /**
     * Allowed question types supported in quiz elements.
     *
     * @var string[]
     */
    private const ALLOWED_QUESTION_TYPES  = ['unique', 'multiple', 'open', 'order'];
    /**
     * Supported asset flags used to decorate elements, questions, and choices.
     *
     * @var string[]
     */
    private const ASSET_FLAGS             = ['image', 'video', 'audio', 'link'];

    /**
     * Parse and validate a JSON string representing a detailed progress report.
     *
     * @param string $json JSON string to decode and process.
     * @return array Parsed and decorated data structure.
     * @throws \JsonException If JSON decoding fails.
     * @throws \coding_exception If validation of the structure fails.
     */
    public static function parse_from_string(string $json): array {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \coding_exception('JSON payload is not an object');
        }
        if (empty($data['structure']) || !is_array($data['structure'])) {
            throw new \coding_exception('Missing or invalid "detailed_progress.structure"');
        }
        $usedids = [];
        foreach ($data['structure'] as $idx => $elem) {
            $data['structure'][$idx] =
                self::validate_and_decorate_element($elem, $usedids, $data);
        }

        return $data;
    }

    /**
     * Validate and enrich a single element from the detailed progress structure.
     *
     * @param array $elem Element to validate and decorate.
     * @param array $usedids Reference to the list of already used element IDs.
     * @param array $rootctx Root context array containing the overall structure.
     * @return array Validated and decorated element with helper flags.
     * @throws \coding_exception If mandatory keys are missing, IDs are duplicated,
     *                           types are invalid, or values are out of range.
     */
    private static function validate_and_decorate_element(array $elem, array &$usedids, array $rootctx): array {
        // Mandatory keys.
        foreach (['id', 'type', 'title', 'completed'] as $k) {
            if (empty($elem[$k]) || !is_string($elem[$k])) {
                throw new \coding_exception("Element missing or invalid '$k'");
            }
        }
        // Unique id check.
        if (in_array($elem['id'], $usedids, true)) {
            throw new \coding_exception("Duplicate id detected: {$elem['id']}");
        }
        $usedids[] = $elem['id'];
        // Allowed type.
        if (!in_array($elem['type'], self::ALLOWED_ELEMENT_TYPES, true)) {
            throw new \coding_exception("Invalid element type: {$elem['type']}");
        }
        // Progress range.
        if (isset($elem['progress']) && (!is_numeric($elem['progress']) || $elem['progress'] < 0 || $elem['progress'] > 100)) {
            throw new \coding_exception("Progress out of range for element {$elem['id']}");
        }

        // Children normalization.
        if (empty($elem['children']) || !is_array($elem['children'])) {
            $elem['children'] = [];
        }

        $typeprop = 'is_' . $elem['type'];
        $elem[$typeprop]        = true;
        $elem['is_chapter']     = $elem['type'] === 'chapter';
        $elem['is_sequence']    = $elem['type'] === 'sequence';
        $elem['is_scenesGroup'] = $elem['type'] === 'scenesGroup';
        $elem['is_scene']       = $elem['type'] === 'scene';
        $elem['is_component']   = $elem['type'] === 'component';
        $elem['has_link']       = !empty($elem['link']);
        $elem['progress']       = $elem['progress'] ?? 0;
        $elem['score']          = $elem['score'] ?? 0;
        $elem['timespent']      = isset($elem['timespent']) ? self::format_duration($elem['timespent']) : 0;;

        // Completion badge status (passed/incomplete/not attempted).
        $completion = $elem['completed'];
        $elem['str_completed'] = get_string(str_replace(' ', '', $completion), 'deffinumreport_detailed');
        $elem['completion_passed']      = $completion === 'passed';
        $elem['completion_incomplete']  = $completion === 'incomplete';
        $elem['completion_notattempted'] = $completion === 'not attempted';
        $elem['completion_failed'] = $completion === 'failed';

        // Asset flags.
        self::add_asset_flags($elem);
        // Children flag for Mustache.
        $elem['is_collapsable'] = count($elem['children']) > 0 || !empty($elem['questions']);

        // Quiz‑specific.
        if ($elem['type'] === 'quiz') {
            $elem['is_quiz'] = true;
            if (empty($elem['questions']) || !is_array($elem['questions'])) {
                throw new \coding_exception("Quiz {$elem['id']} has no questions array");
            }
            foreach ($elem['questions'] as &$q) {
                self::validate_and_decorate_question($q, $elem['id']);
            }
            unset($q);
        }

        foreach ($elem['children'] as $k => $child) {
            $elem['children'][$k] = self::validate_and_decorate_element($child, $usedids, $rootctx);
        }

        return $elem;
    }

    /**
     * Validate and enrich a single quiz question within an element.
     *
     * @param array $q Question data to validate and decorate.
     * @param string $quizid Identifier of the parent quiz element.
     * @return void
     * @throws \coding_exception If question is missing required fields,
     *                           has an invalid type, or lacks choices when required.
     */
    private static function validate_and_decorate_question(array &$q, string $quizid): void {
        if (empty($q['id']) || empty($q['type'])) {
            throw new \coding_exception("Question missing id/type in quiz $quizid");
        }
        if (!in_array($q['type'], self::ALLOWED_QUESTION_TYPES, true)) {
            throw new \coding_exception("Invalid question type '{$q['type']}' in quiz $quizid");
        }

        // Title.
        $q['title'] = !isset($q['title']) || empty($q['title']) ?
            get_string('media', 'deffinumreport_detailed') : $q['title'];
        $q['qtitle'] = $q['title'];
        unset($q['title']);

        // Completion.
        if (!isset($q['completed']) || $q['completed'] === '') {
            $q['completed'] = 'not attempted';
        }
        $completion = $q['completed'];
        $q['str_completed'] = get_string(str_replace(' ', '', $completion), 'deffinumreport_detailed');
        $q['completion_passed'] = $completion === 'passed';
        $q['completion_incomplete'] = $completion === 'incomplete';
        $q['completion_notattempted'] = $completion === 'not attempted';
        $q['completion_failed'] = $completion === 'failed';

        // Type.
        $q['str_type']    = get_string('plaintext_' . $q['type'], 'deffinumreport_detailed');
        $q['is_unique']   = $q['type'] === 'unique';
        $q['is_multiple'] = $q['type'] === 'multiple';
        $q['is_open']     = $q['type'] === 'open';
        $q['is_order']    = $q['type'] === 'order';

        // Asset flags.
        self::add_asset_flags($q);
        // Choice validation & decoration.
        if ($q['type'] !== 'open') {
            if (empty($q['choices']) || !is_array($q['choices'])) {
                throw new \coding_exception("Question {$q['id']} has no choices array");
            }
            $q['hasChoices'] = true;
            foreach ($q['choices'] as &$choice) {
                if (empty(trim($choice['text']))) {
                    $choice['text'] = "#{$choice['id']}";
                }
                self::add_asset_flags($choice);
            }
            unset($choice);
        }

        // Substituer les IDs par les libellés utilisateur-friendly.
        self::deffinum_resolve_user_answers($q);
    }

    /**
     * Add asset type flags (image, video, audio, link) to a node.
     *
     * @param array $node Node (element, question, or choice) to decorate.
     * @return void
     */
    private static function add_asset_flags(array &$node): void {
        if (!isset($node['asset']['type'])) {
            foreach (self::ASSET_FLAGS as $flag) {
                $node['asset_'.$flag] = false;
            }
            return;
        }
        $type = $node['asset']['type'];
        foreach (self::ASSET_FLAGS as $flag) {
            $node['asset_'.$flag] = ($type === $flag);
        }
    }

    /**
     * Replace user answer IDs with user-friendly labels based on available choices.
     *
     * @param array $q Question containing user answers and choices.
     * @return void
     */
    private static function deffinum_resolve_user_answers(array &$q): void {
        if (empty($q['userAnswers'])) {
            return;
        }

        // Construct mapping id to text.
        $labelmap = [];
        if (!empty($q['choices'])) {
            foreach ($q['choices'] as $choice) {
                $labelmap[$choice['id']] = !empty(trim($choice['text']))
                    ? trim($choice['text'])
                    : "#{$choice['id']}";
            }
        }

        // Replace.
        $resolved = array_map(
            static fn(string $aid): string => $labelmap[$aid] ?? $aid,
            $q['userAnswers']
        );
        $q['userAnswers_display'] = implode(', ', $resolved);
    }

    /**
     * Format a duration expressed in seconds into a human-readable string.
     *
     * Examples:
     *   65  -> "1m 5s"
     *   3605 -> "1h 5s"
     *
     * @param int $seconds Number of seconds to format.
     * @return string Human-readable duration string.
     */
    public static function format_duration(int $seconds): string {
        if ($seconds <= 0) {
            return '0s';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingseconds = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }

        if ($remainingseconds > 0 || empty($parts)) {
            $parts[] = $remainingseconds . 's';
        }

        return implode(' ', $parts);
    }

}
