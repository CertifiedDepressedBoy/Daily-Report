<?php

namespace App\Filament\Resources;

use App\Filament\App\Resources\DailyReportResource\RelationManagers\CommentsRelationManager;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\DailyReport;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DailyReportResource\Pages;
use App\Filament\Resources\DailyReportResource\RelationManagers;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class DailyReportResource extends Resource
{
    protected static ?string $model = DailyReport::class;

    protected static ?string $navigationIcon = 'heroicon-s-cloud-arrow-down';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->disabled(),
                Forms\Components\Select::make('task')
                ->relationship('task' , 'title')
                    ->required()
                    ->disabled(),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::user()->id),
                RichEditor::make('content')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'link',
                        'h2',
                        'h3',
                        'blockquote',
                        'codeBlock'
                    ])
                    ->fileAttachmentsVisibility('public')
                    ->disabled()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('date' , 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                ->label('Employee Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task.title')
                    ->searchable(),
                TextColumn::make('content')
                    ->html()
                    ->formatStateUsing(fn($state) => str_replace('src="http://localhost/storage/', 'src="/storage/', $state)),
                //     ->hidden(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user.name')
                    ->relationship('user' , 'name')
                    ->label('Employee')
                    ->preload()
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
            // 'create' => Pages\CreateDailyReport::route('/create'),
            'edit' => Pages\EditDailyReport::route('/{record}/edit'),
            'view' => Pages\ViewDailyReport::route('/{record}/view'),
        ];
    }
}
