<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'dicds_help_tickets';

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'email',
        'status',
        'priority',
        'response',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }
}
