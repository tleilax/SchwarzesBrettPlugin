<section class="contentbox schwarzesbrett-widget">
    <section>
        <ul>
        <? foreach ($categories as $category): ?>
            <li><?= htmlReady($category->titel) ?></li>
        <? endforeach; ?>
        </ul>
    </section>
</section>

