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
        <a href="/civicrm/activity?action=view&reset=1&id={$activity.id}&cid={$contactId}&context=activity&searchContext=activity" >View</a>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
