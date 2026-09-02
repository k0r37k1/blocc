<?php

namespace App\Filament\Resources\Comments;

use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\Comments\Tables\CommentsTable;
use App\Models\Comment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    private static ?int $pendingCountCache = null;

    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getModelLabel(): string
    {
        return __('Comment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Comments');
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = self::pendingCount();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationUrl(): string
    {
        if (self::pendingCount() > 0) {
            return static::getUrl(name: null, parameters: ['pending' => 1]);
        }

        return static::getUrl();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        if (self::pendingCount() > 0) {
            return __('Pending comments — click to review');
        }

        return null;
    }

    private static function pendingCount(): int
    {
        return self::$pendingCountCache ??= Comment::pending()->count();
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
        ];
    }
}
