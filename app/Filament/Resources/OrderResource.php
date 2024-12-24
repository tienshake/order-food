<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([

                                Hidden::make('user_id')
                                    ->default(fn() => \Illuminate\Support\Facades\Auth::id()),

                                TextInput::make('user_name')
                                    ->default(fn() => \Illuminate\Support\Facades\Auth::user()->name)
                                    ->label('Customer')
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'processing' => 'Processing',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required(),

                                TextInput::make('total_amount')
                                    ->prefix('$')
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $total = 0;
                                        foreach ($get('orderDetails') as $item) {
                                            $total += $item['price'] * $item['quantity'];
                                        }
                                        $set('total_amount', $total);
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('order_date')
                                    ->type('datetime-local')
                                    ->default(now()),


                            ])
                            ->columns(2),

                        Section::make('Order Items')
                            ->schema([
                                Repeater::make('orderDetails')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    $set('price', $product?->price);

                                                    $total = collect($get('orderDetails') ?? [])->reduce(function ($total, $item) {
                                                        return $total + ($item['price'] * ($item['quantity'] ?? 1));
                                                    }, 0);
                                                    $set('total_amount', $total);
                                                }
                                            }),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->minValue(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get, $livewire) {
                                                // Set total cho item hiện tại
                                                $currentPrice = $get('price');
                                                if ($currentPrice) {
                                                    $total = $currentPrice * $state;
                                                    $set('price', $total);
                                                }

                                                // Tính tổng
                                                $total = collect($livewire->data['orderDetails'])->sum(
                                                    fn($item) =>
                                                    $item['price'] * $item['quantity']
                                                );
                                                $livewire->data['total_amount'] = $total;
                                            }),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled()
                                            ->dehydrated()
                                    ])
                                    ->columns(3)
                            ])
                    ])
                    ->columnSpan(['lg' => 2]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->money(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Columns\TextColumn::make('order_date')->dateTime()
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }


    // public static function canEdit(Model $record): bool
    // {
    //     return false;
    // }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
