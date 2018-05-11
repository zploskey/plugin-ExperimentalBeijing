<?php
$title = __('Chapters');
echo head(array(
    'title' => $title,
    'bodyclass' => 'chapters-index',
));
?>

<h1><?php echo $title; ?></h1>

<div class="chapters">
  <?php $i = 1; ?>
  <div class="part">
    <ul class="chapter-list only">
      <li class="chapter-number"><?php echo __('Prologue'); ?></li>
      <li class="chapter-name">
        <a href="<?php echo public_url('exhibits/show/worldly-fables'); ?>">
        <?php echo __('Worldly Fables'); ?>
        </a>
      </ul>
    </ul>
  </div>
  <div class="part">
    <ul class="chapter-list only">
      <li class="chapter-number"><?php echo __('Introduction'); ?></li>
      <li class="chapter-name">
        <a href="<?php echo public_url('exhibits/show/chinese-art-expanded'); ?>">
        <?php echo __('Chinese Contemporary Art in the Expanded Field'); ?>
        </a>
      </li>
    </ul>
  </div>
  <div class="part">
    <div class="title">
      <div class="part-title"><?php echo __('Part I'); ?></div>
      <div class="part-subtitle"><?php echo __('Art Worldings'); ?></div>
    </div>
    <ul class="chapter-list">
      <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
      <li class="chapter-name">
          <a href="<?php echo public_url('exhibits/show/xianfeng-beijing'); ?>">
          <?php echo __('<em>Xianfeng</em> Beijing'); ?>
          </a>
      </li>
      <li class="chapter-number"><?php echo __('Chapter %d', $i++); ?></li>
      <li class="chapter-name">
          <a href="<?php echo public_url('exhibits/show/showcase-beijing'); ?>">
          <?php echo __('Showcase Beijing'); ?></a>
      </li>
    </ul>
  </div>
  <div class="part">
    <div class="title">
      <div class="part-title"><?php echo __('Part II'); ?></div>
      <div class="part-subtitle"><?php echo __('Zones of Encounter'); ?></div>
    </div>
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
  </div>
  <div class="part">
    <div class="title">
      <div class="part-title"><?php echo __('Part III'); ?></div>
      <div class="part-subtitle"><?php echo __('Feminist Sight Lines'); ?></div>
    </div>
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
  </div>
  <div class="part">
    <ul class="chapter-list only">
      <li class="chapter-number"><?php echo __('Epilogue'); ?></li>
        <li class="chapter-name">
            <a href="<?php echo public_url('exhibits/show/recursive-worldly-fables'); ?>">
            <?php echo __('Recursive Worldly Fables'); ?>
            </a>
        </li>
      </ul>
    </ul>
  </div>
</div>

<br/><br/><br/><br/>
