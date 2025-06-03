<x-mail::message>

{{ __('group.owner_has_invited_you_to_join_their_group',[
    'owner' => $group->user->name,
    'group_name' => $group->name,
]) }}
{{ __('group.link_will_expire_in_twenty_four_hours') }}

<x-mail::button :url="$url">
    {{  __('group.join_the_group') }}
</x-mail::button>

{{ __('messages.thank_you') }},<br>
{{ config('app.name') }}
</x-mail::message>
