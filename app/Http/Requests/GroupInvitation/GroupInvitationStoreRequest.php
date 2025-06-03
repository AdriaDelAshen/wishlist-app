<?php

namespace App\Http\Requests\GroupInvitation;

use Illuminate\Foundation\Http\FormRequest;

class GroupInvitationStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emails' => [
                'required',
                'array'
            ],
            'emails.*' => [
                'email',
                'max:255',
                'required'
            ],
            'force_send_email_to_pending_invites' => [
                'boolean',
                'required'
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            foreach ($this->emails as $index => $email) {
                if($this->group->users()->firstWhere('email', $email)) {
                    $validator->errors()->add("emails.$index", __('validation.custom.group_invitation.already_in_group'));
                } elseif($this->group->invitations()->notExpired()->notAccepted()->firstWhere('email', $email) && !$this->force_send_email_to_pending_invites) {
                    $validator->errors()->add("emails.$index", __('validation.custom.group_invitation.is_still_pending'));
                }
            }
        });
    }
}
