<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole\Role;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $guarded = ['id'];
    protected $appends = [
        'role_label',
        'full_name',
        'total_followers',
        'total_following',
        'is_following',
        'avg_rating',
        'total_reviews',
        'is_favourite',
        'is_banned',
        'is_suspended',
        'unread_conversations_count',
        'is_subscribed',
        'unread_notifications',
        // 'region',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expire_at',
        'email_verified_at',
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
            'banned_at' => 'datetime',
            'suspended_until' => 'datetime',
            'suspended_at' => 'datetime',
            // 'role' => Role::class
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    //attributes
    public function getFirstNameAttribute($value)
    {
        if ($this->role == Role::MEMBER) {
            return $value;
        } elseif ($this->role == Role::STORE) {
            return $this->attributes['store_name'] ?? $value;
        } elseif ($this->role == Role::BRAND) {
            return $this->attributes['brand_name'] ?? $value;
        } else {
            return $this->attributes['name'] ?? $value;
        }
        return $value;
    }


    // public function getRoleLabelAttribute(): string
    // {
    //     return $this->role ? Role::from($this->role)->label() : '';
    // }

    public function getRoleLabelAttribute(): string
    {
        return $this->role ? Role::from($this->role)->label() : '';
    }

    //full  name
    public function getFullNameAttribute(): string
    {
        if($this->last_name === 'null' || $this->last_name === null){
            return $this->first_name;
        }
        $fullName = $this->first_name . ' ' . $this->last_name;

        return $fullName;
    }

    //get avatar
    public function getAvatarAttribute($value)
    {
        $fullName = $this->first_name;

        if (!empty($this->last_name)) {
            $fullName .= '+' . $this->last_name;
        }

        $encodedName = urlencode($fullName);

        return $value
            ? asset('storage/' . $value)
            : "https://ui-avatars.com/api/?background=random&name={$encodedName}&bold=true";
    }


    //get cover photo
    public function getCoverPhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : asset('images/default-cover.png');
    }

    //get region name
    // public function getRegionAttribute(): string
    // {
    //     return $this->address?->region?->name ?? '';
    // }





    //relationships

    //favorites
    public function favourites()
    {
        return $this->belongsToMany(user::class, 'favourites', 'user_id', 'favourite_id')
            ->withTimestamps();
    }

    //favorites by
    public function favouritesBy()
    {
        return $this->belongsToMany(user::class, 'favourites', 'favourite_id', 'user_id')
            ->withTimestamps();
    }

    //address
    // public function address()
    // {
    //     return $this->hasOne(Address::class);
    // }
     public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function manageProducts()
    {
        return $this->hasMany(ManageProduct::class);
    }

    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }

    //relationship with wholesaler products
    public function wholesalerProducts()
    {
        return $this->hasMany(WholesalerProduct::class);
    }

    //relationship with reviews on manage products
    public function reviewsOnManageProducts()
    {
        return $this->hasManyThrough(
            Review::class,
            ManageProduct::class,
            'user_id',          // manage_products table foreign key
            'manage_product_id', // reviews table foreign key
            'id',              // user table local key
            'id'                // manage_products table local key
        );
    }

    //relationship with reviews on store products
    public function reviewsOnStoreProducts()
    {
        return $this->hasManyThrough(
            Review::class,
            StoreProduct::class,
            'user_id',          // store_products table foreign key
            'store_product_id', // reviews table foreign key
            'id',              // user table local key
            'id'                // store_products table local key
        );
    }

    //relationship with checkouts
    public function checkouts()
    {
        return $this->hasMany(Checkout::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')->withTimestamps();
    }

    //get total followers
    public function getTotalFollowersAttribute(): int
    {
        return $this->followers()->count();
    }
    //get total following
    public function getTotalFollowingAttribute(): int
    {
        return $this->following()->count();
    }

    //is following attribute
    public function getIsFollowingAttribute(): bool
    {
        return $this->followers()->where('follower_id', auth()->id())->exists();
    }


    //forum groups
    public function forumGroups()
    {
        return $this->hasMany(ForumGroup::class);
    }


    //relationship with about us
    public function about()
    {
        return $this->hasOne(About::class);
    }


    //reviews relationship
    public function storeReviews()
    {
        return $this->hasManyThrough(Review::class, StoreProduct::class);
    }

    public function brandReviews()
    {
        return $this->hasManyThrough(Review::class, ManageProduct::class);
    }

    public function wholesalerReviews()
    {
        return $this->hasManyThrough(Review::class, WholesalerProduct::class);
    }

    //all reviews
    public function allReviews()
    {
        return $this->hasMany(Review::class)->whereNull('parent_id');
    }

    //average rating attribute
    public function getAvgRatingAttribute()
    {

        $average = match ($this->role) {
            Role::STORE->value => $this->storeReviews()->avg('rating'),
            Role::BRAND->value => $this->brandReviews()->avg('rating'),
            Role::WHOLESALER->value => $this->wholesalerReviews()->avg('rating'),
            default => 0,
        };

        return round($average ?? 0, 1);
    }

    //total reviews attribute
    public function getTotalReviewsAttribute(): int
    {
        return match ($this->role) {
            Role::STORE->value => $this->storeReviews()->count(),
            Role::BRAND->value => $this->brandReviews()->count(),
            Role::WHOLESALER->value => $this->wholesalerReviews()->count(),
            default => 0,
        };
    }

    public function getIsFavouriteAttribute(): bool
    {
        return $this->favouritesBy()->where('user_id', auth()->id())->exists();
    }

    //is banned attribute
    public function getIsBannedAttribute(): bool
    {
        return $this->banned_at !== null;
    }
    //is suspended helper
    public function isSuspended(): bool
    {
        return $this->suspended_until && Carbon::now()->lessThan($this->suspended_until);
    }

    //is suspended attribute
    public function getIsSuspendedAttribute(): bool
    {
        return $this->suspended_at !== null && $this->isSuspended();
    }

    //unread senders count attribute
    public function getUnreadConversationsCountAttribute()
    {

        return Message::where('receiver_id', $this->id)
            ->where('is_read', 0)
            ->distinct('sender_id')
            ->count();
    }

    /**
     *
     * The reviews that are liked by the user.
     */
    public function likedReviews()
    {
        return $this->belongsToMany(Review::class, 'reviews_likeables', 'user_id', 'review_id');
    }

    public function paymentGatewayCredential()
    {
        return $this->hasOne(PaymentGatewayCredential::class)->where('gateway_name', 'authorizenet');
    }
    public function b2bProviders()
    {
        return $this->belongsToMany(User::class, 'b2b_connections', 'requester_id', 'provider_id')->withPivot('status');
    }
    public function b2bRequesters()
    {
        return $this->belongsToMany(User::class, 'b2b_connections', 'provider_id', 'requester_id')->withPivot('status');
    }

    //subscription relationship
    // public function subscriptions()
    // {
    //     return $this->hasMany(Subscription::class);
    // }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscribable');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('invoice_status', 'paid')
            ->where('ends_at', '>', now())
            ->exists();
    }

    //is subscribed attribute
    public function getIsSubscribedAttribute(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function joinedForumGroups()
    {
        return $this->belongsToMany(ForumGroup::class, 'forum_group_members')
            ->withPivot('status')
            ->withTimestamps();
    }

    //unread notifications count attribute
    public function getUnreadNotificationsAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    //branches relationship
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
