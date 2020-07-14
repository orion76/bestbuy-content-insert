<div class="custom-banner">

    <div class="custom-banner__label">{$sVars.banner_label}</div>
    <div class="custom-banner__block">
        <div class="custom-banner__column-1">
            <div class="custom-banner__logo">
                <img src="{$sVars.banner_logo}" alt="{$sVars.banner_top}">
            </div>
            <div class="custom-banner__image">
                <img src="{$sVars.banner_image}" alt="{$sVars.banner_top}">
            </div>
        </div>
        <div class="custom-banner__column-2">
            <div class="custom-banner__top">
                <h3>{$sVars.banner_top}</h3>
            </div>
            <div class="custom-banner__content">
                <ul class="custom-banner-content">
                    {foreach $sVars.banner_content as $listItem}
                        <li class="custom-banner-content__item">
                            <div class="custom-banner-content__item-marker"><i class="icon--check"></i></div>
                            <div class="custom-banner-content__item-text">
                                {$listItem}
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="custom-banner__button">
                <a href="{$sVars.banner_link_url}">{$sVars.banner_link_label}</a>
            </div>
        </div>


    </div>
</div>
