{*
* 2006-2022 THECON SRL
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* YOU ARE NOT ALLOWED TO REDISTRIBUTE OR RESELL THIS FILE OR ANY OTHER FILE
* USED BY THIS MODULE.
*
* @author    THECON SRL <contact@thecon.ro>
* @copyright 2006-2022 THECON SRL
* @license   Commercial
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'th_reindexing_product_categories'}
        <div class="form-group">
            <div class="col-xs-12 col-lg-8 {if $input.th_ps_version eq '7' && $input.th_ps_sub_version eq '8'}col-lg-offset-4{else}col-lg-offset-3{/if}">
                <button type="submit" value="1" class="btn btn-secondary" name="submit_th_reindex">
                    <img src="{$input.th_icon_path|escape:'htmlall':'UTF-8'}" alt="" class="th_reindexing_products_cat_icon">
                    {l s='Reindex' mod='thprodupdmaincat'}
                </button>
            </div>
        </div>
    {elseif $input.type == 'th_html'}
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-8">
                {$input.html_content nofilter}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
