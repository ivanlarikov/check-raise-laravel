<?php

namespace Database\Seeders;

use App\Models\Notification\Notification;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // #MARK: Player
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'register_tournament',
                'title' => json_encode([
                    'en' => 'Registering to a tournament',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode(['room_title', 'room_address', 'tournament_title', 'tournament_date']),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'register_tournament_waiting_list',
                'title' => json_encode([
                    'en' => 'Registering to a tournament in waiting list',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'room_address',
                    'tournament_title',
                    'tournament_date'
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => false,
                'slug' => 'unregister_tournament',
                'title' => json_encode([
                    'en' => 'Unregistering from a tournament',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'room_address',
                    'tournament_title',
                    'tournament_date'
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'enter_tournament_from_waiting',
                'title' => json_encode([
                    'en' => 'Entering the tournament from the waiting list',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'room_address',
                    'tournament_title',
                    'tournament_date'
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'manager_suspend_player',
                'title' => json_encode([
                    'en' => 'RM suspending a player',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'manager_unsuspend_player',
                'title' => json_encode([
                    'en' => 'RM unsuspending a player',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'register_email_confirm',
                'title' => json_encode([
                    'en' => 'Registration e-mail confirmation',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'token',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'change_email_confirm',
                'title' => json_encode([
                    'en' => 'Email change confirmation',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'token',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'manual_register',
                'title' => json_encode([
                    'en' => 'Manual registration welcome email',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'email',
                    'password'
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'player',
                'status' => true,
                'slug' => 'reset_password',
                'title' => json_encode([
                    'en' => 'Reset password email',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'token',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // #MARK: Manager
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'buy_credits',
                'title' => json_encode([
                    'en' => 'Purchasing credits',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'amount',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'admin_modified_credits',
                'title' => json_encode([
                    'en' => 'Credits modified by Admin',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'amount',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'buy_banner',
                'title' => json_encode([
                    'en' => 'Purchasing a banner',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'banner_type',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'buy_premium_tournament',
                'title' => json_encode([
                    'en' => 'Purchasing a premium tournament',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'contact',
                'title' => json_encode([
                    'en' => 'Contact RM from contact form',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'email',
                    'message',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => true,
                'slug' => 'subscription_modified',
                'title' => json_encode([
                    'en' => 'Subscription modified',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => false,
                'slug' => 'weekly_report',
                'title' => json_encode([
                    'en' => 'Weekly report',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'tournaments',
                    'registered_players',
                    'players_without_check_in',
                    'players_without_check_in_percentage',
                    'players_with_check_in',
                    'players_with_check_in_percentage',
                    're_entries',
                    're_entries_percentage',
                    'cumulated_prize_pools',
                    'cumulated_rakes',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'manager',
                'status' => false,
                'slug' => 'monthly_report',
                'title' => json_encode([
                    'en' => 'Monthly report',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'tournaments',
                    'registered_players',
                    'players_without_check_in',
                    'players_without_check_in_percentage',
                    'players_with_check_in',
                    'players_with_check_in_percentage',
                    're_entries',
                    're_entries_percentage',
                    'cumulated_prize_pools',
                    'cumulated_rakes',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // #MARK: Admin
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'new_player',
                'title' => json_encode([
                    'en' => 'New Player account',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'email',
                    'firstname',
                    'lastname',
                    'nickname',
                    'dob',
                    'address',
                    'phone',
                    'displayoption',
                    'language',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'new_tournament',
                'title' => json_encode([
                    'en' => 'New Tournament',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'tournament_title',
                    'tournament_date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'new_transaction',
                'title' => json_encode([
                    'en' => 'New Transaction (PayPal)',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'amount',
                    'transaction_date',
                    'id',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'rm_buy_banner',
                'title' => json_encode([
                    'en' => 'RM purchasing a banner',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'banner_type',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'rm_buy_premium',
                'title' => json_encode([
                    'en' => 'RM purchasing a premium tournament',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'date',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'contact_admin',
                'title' => json_encode([
                    'en' => 'Contact Admin from contact form',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'firstname',
                    'lastname',
                    'email',
                    'message',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'rm_suspend',
                'title' => json_encode([
                    'en' => 'RM suspended',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'email',
                    'firstname',
                    'lastname',
                    'nickname',
                    'dob',
                    'phone',
                    'address',
                    'displayoption',
                    'language',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type' => 'admin',
                'status' => true,
                'slug' => 'rm_unsuspend',
                'title' => json_encode([
                    'en' => 'RM unsuspended',
                    'fr' => '',
                    'de' => ''
                ]),
                'variables' => json_encode([
                    'room_title',
                    'email',
                    'firstname',
                    'lastname',
                    'nickname',
                    'dob',
                    'phone',
                    'address',
                    'displayoption',
                    'language',
                ]),
                'content' => json_encode(['en' => '', 'fr' => '', 'de' => '']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Notification::insert($data);
    }
}
