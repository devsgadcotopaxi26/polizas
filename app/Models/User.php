<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\EstablecerContrasenaNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'certificado_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Usuarios');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'certificado_path',
        'must_change_password',
        'is_active',
        'can_view_other_polizas',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'can_view_other_polizas' => 'boolean',
        ];
    }

    /**
     * Pólizas creadas por este usuario
     */
    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'created_by');
    }

    /**
     * Envía el enlace para establecer/restablecer la contraseña por el
     * mailer 'sistema', en vez del correo genérico de Laravel.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new EstablecerContrasenaNotification($token));
    }
}
