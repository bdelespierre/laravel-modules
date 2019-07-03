<?php

namespace SystemModules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use SystemModules\Core\App\Facades\ModulesManager;

/**
 * Class Module
 *
 * @package SystemModules\Core\App\Models
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $path
 * @property string $loadParameters
 * @property string $providers
 * @property string $aliases
 * @property bool $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Module extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'alias' => 'string',
        'keywords' => 'array',
        'path' => 'string',
        'loadParameters' => 'array',
        'providers' => 'array',
        'aliases' => 'array',
        'active' => 'boolean',
    ];

    /**
     * The list of the system modules aliases, this modules can't be altered.
     *
     * @var array
     */
    const SYS_MODULES = [
        'core',
    ];

    /**
     * Execute a query for a single record by alias.
     *
     * @param $alias string
     * @return Module
     */
    public static function findAlias($alias)
    {
        return static::where('alias', $alias)->first();
    }

    /**
     * Execute a query for a single record by alias or throw an exception.
     *
     * @param $alias string
     * @return Module
     */
    public static function findAliasOrFail($alias)
    {
        return static::where('alias', $alias)->firstOrFail();
    }

    /**
     * Update the module config in composer.json under extra.laravel-modules.
     *
     * @param $config
     * @param $value
     * @return bool
     */
    public function setConfig($config, $value)
    {
        return ModulesManager::setConfig($this, $config, $value);
    }

    /**
     * Return true if the module is active
     *
     * @return boolean
     */
    public function isActive()
    {
        if ($this->active)
            return true;
        else
            return false;
    }

    /**
     * Return true if it's a system module
     *
     * @return boolean
     */
    public function isSystem()
    {
        return in_array($this->getAlias(), self::SYS_MODULES);
    }

    /**
     * Get the alias of the module.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Enable the module
     *
     * @return boolean
     */
    public function enable()
    {
        $this->active = true;

        return $this->save();
    }

    /**
     * Disable the module
     *
     * @return boolean
     */
    public function disable()
    {
        $this->active = false;

        return $this->save();
    }

    /**
     * Uninstall the module
     *
     * @return bool|\SystemModules\Core\App\Services\ModulesManager
     * @throws \Exception
     */
    public function uninstall()
    {
        return parent::delete();
    }

    /**
     * Delete the module
     *
     * @return bool|\SystemModules\Core\App\Services\ModulesManager|null
     * @throws \Exception
     */
    public function delete()
    {
        return parent::delete() ? ModulesManager::delete($this) : false;
    }

    public function path($path)
    {
        return $this->path . $path;
    }
}