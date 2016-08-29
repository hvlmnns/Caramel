{extends file="_source/body.tpl"}


{block name="page-wrapper:classes"}has-breadcrumbs is-docs{/block}

{block name="content"}
    {function name="docsMenu" menu=false path="index"}
        {if is_array($menu)}
            <ul class="menu-list list-unstyled">
                <li>
                    <a href="{$pathPrefix}" title="Overview" {if $pageName == "index"}class="active"{/if}>
                        Overview
                    </a>
                </li>
                {foreach $menu as $item}
                    <li>
                        {if $item.link}
                            <a href="{$pathPrefix}{$item.link}" title="{$item.name}" {if $item.escapedName == $pageName}class="active"{/if}>
                                {$item.name}
                            </a>
                        {/if}
                    </li>
                {/foreach}
            </ul>
        {/if}
    {/function}

    {function name="docsSubMenu" menu=false position="breadcrumbs" path="index"}
        {if is_array($menu) && $menu|@count > 0}
            <div class="docs-on-this-page">
                <h4 class="text-upper">on this page</h4>
                <ul class="menu-list list-unstyled">
                    {if $position != "top"}
                        <li class="doc-link">
                            <a href="#docs-sidebar" title="back to top">back to top</a>
                        </li>
                    {/if}
                    {foreach $menu as $item}
                        <li class="doc-link">
                            <a href="#{$item.escapedName}" title="{$item.name}">{if $position == "top"}-{/if} {$item.name}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/if}
    {/function}
    <div class="container">
        <div class="row border-left-light">
            <div class="col-xs-3 col-xs-pull-3 no-padding-left">
                <div class="jump-docs-sidebar docs-page-jumper no-index"></div>
                <div class="sidebar">
                    {call docsMenu menu=$docsMenu}
                </div>
                <div class="breadcrumbs fade">
                    <div class="breadcrumbs-bg">
                        <div class="sidebar">
                            {call docsSubMenu menu=$docsSubMenu}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="docs"> 
                    <div class="head">
                        <h1>
                            {$docTitle}
                        </h1>
                        <p>
                            {$docText}
                        </p>
                        {call docsSubMenu menu=$docsSubMenu position="top"}
                    </div>
                    <div class="docs-sections">
                        {block name="content:docs"}

                        {/block}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}