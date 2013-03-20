<?php
/**
 * Autofoldertron
 *
 * @version 1.0
 * @author John Noel <john.noel@rckt.co.uk>
 * @package Autofoldertron
 */

$event = &$modx->event;
$resource = &$event->params['resource'];

/** @var array An array of template IDs to act as parents */
$parentTemplates = array_filter(array_map('intval', explode(',', $modx->getOption('parent_templates', $scriptProperties, ''))));

if (empty($parentTemplates)) {
    $modx->log(modX::LOG_LEVEL_INFO, '[Autofoldertron] No valid parent template IDs supplied');
    return false;
}

$generatedPageTemplate = intval($modx->getOption('generated_template', $scriptProperties, '')); // what template will generated pages have?
// prevent recursive nightmare
if (in_array($generatedPageTemplate, $parentTemplates)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Autofoldertron] Generated page template MUST NOT be the same as parent templates');
    return false;
}

$folderStructure = array_filter(array_map('trim', explode('/', strtolower($modx->getOption('folder_structure', $scriptProperties, 'y/m'))))); // what will be the folder structure beneath the parent template?
$filterTemplates = array_filter(array_map('intval', explode(',', $modx->getOption('filter_templates', $scriptProperties, ''))));
$dateField = $modx->getOption('date_field', $scriptProperties, 'publishedon'); // what will be used as the date field?

$yearAliasFormat = $modx->getOption('year_alias_format', $scriptProperties, 'Y'); // alias format for the generated year folder
$yearTitleFormat = $modx->getOption('year_title_format', $scriptProperties, 'Y'); // title format for the generated year folder
$monthAliasFormat = $modx->getOption('month_alias_format', $scriptProperties, 'm'); // alias format for the generated month folder
$monthTitleFormat = $modx->getOption('month_title_format', $scriptProperties, 'F'); // title format for the generated month folder
$dayAliasFormat = $modx->getOption('day_alias_format', $scriptProperties, 'd'); // alias format for the generated day folder
$dayTitleFormat = $modx->getOption('day_title_format', $scriptProperties, 'd'); // title format for the generated day folder

// TODO check for valid formats?

// check template of the resource is within the filter templates array
if (empty($filterTemplates) || !in_array(intval($resource->get('template')), $filterTemplates)) {
    return false;
}

// fetch parent resource
$parentId = $resource->get('parent');
$parent = $modx->getObject('modResource', $parentId);
if ($parent === null) {
    $modx->log(modX::LOG_LEVEL_INFO, '[Autofoldertron] No valid parent detected for resource '.$resource->get('id'));
    return false;
}

if (!in_array(intval($parent->get('template')), $parentTemplates)) {
    $modx->log(modX::LOG_LEVEL_INFO, '[Autofoldertron] Parent template not in parentTemplates');
    return false;
}

$workingDateTime = new DateTime($resource->get($dateField));

$lastParent = $parent;
// this array should map folderStructure[k] => resource
$resourceStructure = array();

foreach ($folderStructure as $folderPart) {
    $aliasSearch = '';

    switch ($folderPart) {
    case 'y':
        $aliasSearch = $workingDateTime->format($yearAliasFormat);
        break;
    case 'm':
        $aliasSearch = $workingDateTime->format($monthAliasFormat);
        break;
    case 'd':
        $aliasSearch = $workingDateTime->format($dayAliasFormat);
        break;
    }

    $children = $modx->getCollection('modResource', array(
        'parent' => $lastParent->get('id'),
    ));
    $found = false;

    foreach ($children as $child) {
        if (($child->get('parent') == $lastParent->get('id')) && ($child->get('alias') == $aliasSearch)) {
            $resourceStructure[] = $child;
            $lastParent = $child;
            $found = true;
            break;
        }
    }

    // haven't found our parent, create it
    if ($found === false) {
        $title = '';
        $menuindex = 0; // force numerical ordering
        switch ($folderPart) {
        case 'y':
            $title = $workingDateTime->format($yearTitleFormat);
            $menuIndex = intval($workingDateTime->format('Y'));
            break;
        case 'm':
            $title = $workingDateTime->format($monthTitleFormat);
            $menuIndex = intval($workingDateTime->format('m'));
            break;
        case 'd':
            $title = $workingDateTime->format($dayTitleFormat);
            $menuIndex = intval($workingDateTime->format('d'));
            break;
        }

        $newResource = $lastParent->duplicate(array(
            'newName' => $title,
            'parent' => $lastParent->get('id'),
            'duplicateChildren' => false,
        ));

        $newResource->set('isfolder', 1);
        $newResource->set('published', 1);
        $newResource->set('searchable', 0);
        $newResource->set('template', $generatedPageTemplate);
        $newResource->set('longtitle', $title);
        $newResource->set('menutitle', $title);
        $newResource->set('alias', $aliasSearch);
        $newResource->set('menuindex', $menuIndex);
        $newResource->save();

        $resourceStructure[] = $newResource;
        $lastParent = $newResource;
    }
}

// TODO check the resourceStructure array for recursive parenting

$menuindex = 0;
$localChildren = $modx->getCollection('modResource', array('parent' => $lastParent->get('id')));
foreach ($localChildren as $localChild) {
    if (intval($localChild->get('menuindex')) > $menuindex) {
        $menuindex = intval($localChild->get('menuindex'));
    }
}

$resource->set('menuindex', $menuindex + 1);
$resource->set('parent', $lastParent->get('id'));
$resource->save();
