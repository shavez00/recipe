<?php ob_start() ?>
<div id="fea" class="features">
    <div class="container">
        <div class="head text-center">
        <h1>Recipes</h1>
        <?php foreach ($Recipe_array['results'] as $recipe): ?>
            <a href="<?php echo $recipe['href'] ?>"><?php echo $recipe['title'] ?></a>
            <br>
        <?php endforeach ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean() ?>
<?php include 'layout.php' ?>
