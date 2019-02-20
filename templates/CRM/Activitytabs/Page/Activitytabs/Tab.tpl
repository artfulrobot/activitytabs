{* This produces the markup for each of the activitytabs tabs *}
<table>
  <thead>
    <tr>
      {foreach from=$columnMap item=col key=i}
        <th>{$col}</th>
      {/foreach}
        <th>View</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$activities name=activityloop key=i item=activity}
    <tr>
      {foreach from=$atabConfig->columns item=col key=j}
        <td>{if $col == 'activity_date_time'}
        {$activity[$col]|date_format:'%d %b %Y %H:%M'}
        {else}
        {$activity[$col]}
        {/if}
        </td>
      {/foreach}
      <td>
        <a href="/civicrm/activity?action=view&amp;reset=1&amp;id={$activity.id}&amp;cid={$contactId}&amp;context=activity&amp;searchContext=activity" >View</a>
        |
        <a href="/civicrm/activity/add?reset=1&amp;action=update&amp;id={$activity.id}&amp;cid={$contactId}&amp;context=activity" >Edit</a>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
