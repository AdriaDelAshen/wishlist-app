<x-mail::message>

{{ __('group.owner_has_removed_your_pending_invitation',[
    'owner' => $groupInvitation->group->user->name,
    'group_name' => $groupInvitation->group->name,
]) }}

{{ __('messages.thank_you') }},<br>
{{ config('app.name') }}
</x-mail::message>
