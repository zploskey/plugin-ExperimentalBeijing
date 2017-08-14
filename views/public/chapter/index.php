<?php
$title = __('Chapters');
echo head(array(
    'title' => $title,
));
?>

<h1><?php echo $title; ?></h1>

<ul class="chapters">
    <?php $i = 1; ?>
    <li class="part-title"><?php echo __('Prologue'); ?></li>
    <li class="part-subtitle">
        <a href="<?php echo public_url('exhibits/show/worldly-fables'); ?>">
        <?php echo __('Worldly Fables'); ?>
        </a>
    </li>
    <li class="part-title"><?php echo __('Introduction'); ?></li>
    <li class="part-subtitle">
        <a href="<?php echo public_url('exhibits/show/chinese-art-expanded'); ?>">
        <?php echo __('Chinese Art in the Expanded Field'); ?>
        </a>
    </li>
    <li class="part-title"><?php echo __('Part I'); ?></li>
    <li class="part-subtitle"><?php echo __('Art Worldings'); ?></li>
    <ul class="chapter-list">
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/xianfeng-beijing'); ?>">
            <?php echo __('Xianfeng Beijing'); ?>
            </a>
        </li>
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/showcase-beijing'); ?>">
            <?php echo __('Showcase Beijing'); ?></li>
            </a>
    </ul>
    <li class="part-title"><?php echo __('Part II'); ?></li>
    <li class="part-subtitle"><?php echo __('Zones of Encounter'); ?></li>
    <ul class="chapter-list">
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/besieged-city'); ?>">
            <?php echo __('The Besieged City'); ?>
            </a>
        </li>
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/hinterlands-feminist-art'); ?>">
            <?php echo __('The Hinterlands of Feminist Art'); ?>
            </a>
        </li>
    </ul>
    <li class="part-title"><?php echo __('Part III'); ?></li>
    <li class="part-subtitle"><?php echo __('Feminist Sightlines'); ?></li>
    <ul class="chapter-list">
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/red-detachment'); ?>">
            <?php echo __('Red Detachment'); ?>
            </a>
        </li>
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/opening-great-wall'); ?>">
            <?php echo __('Opening the Great Wall'); ?>
            </a>
        </li>
        <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/camouflaged-histories'); ?>">
            <?php echo __('Camouflaged Histories'); ?>
            </a>
        </li>
    </ul>
    <li class="part-title"><?php echo __('Epilogue'); ?></li>
    <li class="part-subtitle">
        <a href="<?php echo public_url('exhibits/show/recursive-worldly-fables'); ?>">
        <?php echo __('Recursive Worldly Fables'); ?>
        </a>
    </li>
</ul>