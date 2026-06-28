<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $vars = [
            'tenant_first_name', 'tenant_name', 'landlord_first_name', 'landlord_name',
            'property_name', 'property_address', 'rent_amount', 'currency_code',
            'due_date', 'due_day', 'days_until_due', 'days_overdue',
            'late_fee_amount', 'lease_end_date', 'days_until_expiry', 'platform_name',
        ];

        $templates = [

            [
                'slug'              => 'rent_reminder_5d',
                'name'              => '5-day rent reminder',
                'trigger_event'     => 'rent_due_in_days',
                'trigger_days'      => 5,
                'trigger_direction' => 'before',
                'sort_order'        => 10,
                'subject'           => 'Reminder: Your rent is due in 5 days — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>This is a friendly reminder that your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> is due on <strong>{{due_date}}</strong> for <strong>{{property_name}}</strong>.</p>
<p>Please ensure funds are available to avoid a late fee.</p>
<p>You can manage your payments at any time through the Renpresso tenant portal.</p>
<p>Thank you,<br>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'rent_reminder_1d',
                'name'              => '1-day rent reminder',
                'trigger_event'     => 'rent_due_in_days',
                'trigger_days'      => 1,
                'trigger_direction' => 'before',
                'sort_order'        => 20,
                'subject'           => 'Your rent is due tomorrow — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>Just a heads-up — your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> is due <strong>tomorrow, {{due_date}}</strong>.</p>
<p>If you have any issues, please contact your landlord as soon as possible.</p>
<p>Thank you,<br>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'rent_due_today',
                'name'              => 'Rent due today',
                'trigger_event'     => 'rent_due_in_days',
                'trigger_days'      => 0,
                'trigger_direction' => 'on',
                'sort_order'        => 30,
                'subject'           => 'Your rent is due today — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>Your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> is due <strong>today</strong> for <strong>{{property_name}}</strong>.</p>
<p>If payment has already been set up automatically, no action is needed.</p>
<p>Thank you,<br>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'rent_overdue_3d',
                'name'              => 'Rent overdue (3 days)',
                'trigger_event'     => 'rent_overdue_days',
                'trigger_days'      => 3,
                'trigger_direction' => 'after',
                'sort_order'        => 40,
                'subject'           => 'Overdue: Rent payment for {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>Your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> was due on {{due_date}} and has not been received.</p>
<p>Your account is now <strong>{{days_overdue}} days overdue</strong>. Please arrange payment immediately to avoid a late fee.</p>
<p>If there is an issue, please contact {{landlord_first_name}} directly through {{platform_name}}.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'rent_overdue_7d',
                'name'              => 'Rent overdue (7 days)',
                'trigger_event'     => 'rent_overdue_days',
                'trigger_days'      => 7,
                'trigger_direction' => 'after',
                'sort_order'        => 50,
                'subject'           => 'Urgent: Rent {{days_overdue}} days overdue — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>Your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> remains unpaid and is now <strong>{{days_overdue}} days overdue</strong>.</p>
<p>Continued non-payment may result in further action in accordance with your lease agreement.</p>
<p>Please contact {{landlord_first_name}} immediately to resolve this.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'late_fee_applied',
                'name'              => 'Late fee notice',
                'trigger_event'     => 'late_fee_applied',
                'trigger_days'      => null,
                'trigger_direction' => null,
                'sort_order'        => 60,
                'subject'           => 'Late fee applied to your account — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>As your rent payment for <strong>{{property_name}}</strong> has not been received within the grace period, a late fee of <strong>{{currency_code}} {{late_fee_amount}}</strong> has been applied to your account.</p>
<p>Please log in to {{platform_name}} to review your balance and settle the outstanding amount.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'payment_received',
                'name'              => 'Payment received confirmation',
                'trigger_event'     => 'payment_success',
                'trigger_days'      => null,
                'trigger_direction' => null,
                'sort_order'        => 70,
                'subject'           => 'Payment received — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>We have successfully received your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> for <strong>{{property_name}}</strong>.</p>
<p>Thank you for your prompt payment. Your receipt is available in your {{platform_name}} tenant portal.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'payment_failed',
                'name'              => 'Payment failed',
                'trigger_event'     => 'payment_failed',
                'trigger_days'      => null,
                'trigger_direction' => null,
                'sort_order'        => 80,
                'subject'           => 'Payment failed — action required for {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>Your rent payment of <strong>{{currency_code}} {{rent_amount}}</strong> for <strong>{{property_name}}</strong> could not be processed.</p>
<p>This may be due to insufficient funds or an expired payment method. Please update your payment details in the {{platform_name}} portal or contact your landlord.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

            [
                'slug'              => 'lease_expiry_30d',
                'name'              => 'Lease expiry notice (30 days)',
                'trigger_event'     => 'lease_expiry_days',
                'trigger_days'      => 30,
                'trigger_direction' => 'before',
                'sort_order'        => 90,
                'subject'           => 'Your lease expires in 30 days — {{property_name}}',
                'body_html'         => <<<HTML
<p>Hi {{tenant_first_name}},</p>
<p>This is a notice that your lease for <strong>{{property_name}}</strong> is due to expire on <strong>{{lease_end_date}}</strong> — that is <strong>30 days from today</strong>.</p>
<p>Please get in touch with {{landlord_first_name}} to discuss renewal options or make arrangements for vacating the property.</p>
<p>{{landlord_first_name}}<br>via {{platform_name}}</p>
HTML,
            ],

        ];

        foreach ($templates as $data) {
            EmailTemplate::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'available_variables' => $vars,
                    'is_published'        => true,
                    'landlord_can_edit'   => true,
                    'landlord_can_disable'=> true,
                ])
            );
        }
    }
}
