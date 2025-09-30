<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadStage extends Model
{
    use HasFactory;
    protected $fillable = [
        'workspace_id',
        'name',
        'order',
        'color',
        'slug'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('defaultOrWorkspace', function (Builder $builder) {
            $workspaceId = getWorkspaceId(); // 🔁 Replace with your actual helper or logic

            $builder->where(function ($query) use ($workspaceId) {
                $query->where(function ($q) {
                    $q->where('is_default', true)->whereNull('workspace_id');
                })->orWhere('workspace_id', $workspaceId);
            });
        });
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    public static function getNextOrderForWorkspace($workspaceId)
    {

        // dd($workspaceId, LeadStage::where('workspace_id', $workspaceId)->get());
        $workspaceMax = self::where('workspace_id', $workspaceId)->max('order');
        $defaultMax = self::whereNull('workspace_id')->where('is_default', 1)->max('order');

        // Prevent null issues (if no record exists yet)
        $maxOrder = max([$workspaceMax ?? 0, $defaultMax ?? 0]);

        return $maxOrder + 1;
    }
}
