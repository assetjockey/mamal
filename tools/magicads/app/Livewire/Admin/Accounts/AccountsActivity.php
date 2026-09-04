<?php

namespace App\Livewire\Admin\Accounts;

use Livewire\Component;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Masmerise\Toaster\Toaster;

class AccountsActivity extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    protected static $currencies;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->queryStringIdentifier('users')
            ->query(fn (): Builder => User::query()->with('latestSession')->whereHas('sessions'))
            ->recordActionsColumnLabel(__('Actions'))
            ->recordClasses('border-b border-(--default-border-color) shadow-none hover:bg-(--default-element-light-bg-color) transition-colors')
            ->columns([
                TextColumn::make('name')
                        ->label(__('User'))
                        ->formatStateUsing(function ($record) {
                            $name = $record->name ?? '';
                            $email = $record->email ?? '';
                            $avatar = $record->avatar;

                            // Resolve the stored avatar to a displayable URL:
                            // social logins store a full http(s) URL, uploads
                            // store a public-disk relative path, and new accounts
                            // carry a placeholder we treat as "no custom avatar".
                            if (blank($avatar) || $avatar === 'img/users/avatar.jpg') {
                                $media = '<span class="inline-flex w-10 h-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white" style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">' . e($record->initials()) . '</span>';
                            } else {
                                $src = \Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://'])
                                    ? $avatar
                                    : asset($avatar);
                                $media = '<img src="' . e($src) . '" alt="' . e($name) . '" class="w-10 h-10 shrink-0 rounded-full object-cover" />';
                            }

                            return '<div class="flex items-center gap-3">' . $media . '<div><div>' . e($name) . '</div><div class="text-sm text-gray-500">' . e($email) . '</div></div></div>';
                        })
                        ->html()
                        ->toggleable()
                        ->searchable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('group')
                        ->label(__('Group'))
                        ->toggleable()
                        ->searchable()
                        ->sortable()
                        ->html()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'user' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-neutral-100 text-gray-800">User</span>',
                            'admin' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-red-100 text-red-800">Admin</span>',
                            'subscriber' => '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-xl bg-green-100 text-green-800">Subscriber</span>',
                        })
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('latestSession.ip_address')
                        ->label(__('IP Address'))
                        ->toggleable()
                        ->searchable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('latestSession.user_agent')
                        ->label(__('Connection'))
                        ->toggleable()
                        ->limit(50)
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),
                TextColumn::make('latestSession.last_activity')
                        ->label(__('Last Activity'))
                        ->formatStateUsing(fn ($state): ?string => filled($state)
                            ? \Illuminate\Support\Carbon::createFromTimestamp($state)->translatedFormat('M j, Y H:i')
                            : null)
                        ->toggleable()
                        ->sortable()
                        ->extraHeaderAttributes([
                            'class' => 'bg-(--default-element-light-bg-color) border-(--default-border-color)',
                        ]),                        
                
            ])
            ->filters([
            ])
            ->headerActions([
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateHeading(__('No user sessions yet')) 
            ->emptyStateDescription(__('Once your users will login, they will appear here.'))
            ->extraAttributes([
                'class' => 'shadow-none rounded-lg overflow-hidden border border-(--default-border-color)',
            ]);

    }


    public function render()
    {
        return view('livewire.admin.accounts.activity')->title(__('Users Activity') . ' | ' . config('app.name'));
    }
}
