<?php namespace iBrand\UEditor\Uploader;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Abstract Class Upload
 * 文件上传抽象类
 *
 *
 * @package iBrand\UEditor\Uploader
 */
abstract class Upload
{
	protected $fileField; //文件域名
	protected $file; //文件上传对象
	protected $base64; //文件上传对象
	protected $config; //配置信息
	protected $oriName; //原始文件名
	protected $fileName; //新文件名
	protected $fullName; //完整文件名,即从当前配置目录开始的URL
	protected $filePath; //完整文件名,即从当前配置目录开始的URL
	protected $fileSize; //文件大小
	protected $fileType; //文件类型
	protected $stateInfo; //上传状态信息,
	protected $stateMap; //上传状态映射表，国际化用户需考虑此处数据的国际化

	abstract function doUpload(); //抽象方法,上传核心方法

	public function __construct(array $config, $request)
	{
		$this->config    = $config;
		$this->request   = $request;
		$this->fileField = $this->config['fieldName'];
		if (isset($config['allowFiles'])) {
			$this->allowFiles = $config['allowFiles'];
		} else {
			$this->allowFiles = [];
		}

		$stateMap       = [
			"SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
			trans("UEditor::upload.upload_max_filesize"),
			trans("UEditor::upload.upload_error"),
			trans("UEditor::upload.no_file_uploaded"),
			trans("UEditor::upload.upload_file_empty"),
			"ERROR_TMP_FILE"           => trans("UEditor::upload.ERROR_TMP_FILE"),
			"ERROR_TMP_FILE_NOT_FOUND" => trans("UEditor::upload.ERROR_TMP_FILE_NOT_FOUND"),
			"ERROR_SIZE_EXCEED"        => trans("UEditor::upload.ERROR_SIZE_EXCEED"),
			"ERROR_TYPE_NOT_ALLOWED"   => trans("UEditor::upload.ERROR_TYPE_NOT_ALLOWED"),
			"ERROR_CREATE_DIR"         => trans("UEditor::upload.ERROR_CREATE_DIR"),
			"ERROR_DIR_NOT_WRITEABLE"  => trans("UEditor::upload.ERROR_DIR_NOT_WRITEABL"),
			"ERROR_FILE_MOVE"          => trans("UEditor::upload.ERROR_FILE_MOVE"),
			"ERROR_FILE_NOT_FOUND"     => trans("UEditor::upload.ERROR_FILE_NOT_FOUND"),
			"ERROR_WRITE_CONTENT"      => trans("UEditor::upload.ERROR_WRITE_CONTENT"),
			"ERROR_UNKNOWN"            => trans("UEditor::upload.ERROR_UNKNOWN"),
			"ERROR_DEAD_LINK"          => trans("UEditor::upload.ERROR_DEAD_LINK"),
			"ERROR_HTTP_LINK"          => trans("UEditor::upload.ERROR_HTTP_LINK"),
			"ERROR_HTTP_CONTENTTYPE"   => trans("UEditor::upload.ERROR_HTTP_CONTENTTYPE"),
			"ERROR_UNKNOWN_MODE"       => trans("UEditor::upload.ERROR_UNKNOWN_MODE"),
		];
		$this->stateMap = $stateMap;
	}

	/**
	 *
	 *
	 *
	 * @return array
	 */

	public function upload()
	{
		$this->doUpload();

		return $this->getFileInfo();
	}

	/**
	 * 上传错误检查
	 *
	 * @param $errCode
	 *
	 * @return string
	 */
	protected function getStateInfo($errCode)
	{
		return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
	}

	/**
	 * 文件大小检测
	 *
	 * @return bool
	 */
	protected function checkSize()
	{
		return $this->fileSize <= ($this->config["maxSize"]);
	}

	/**
	 * 获取文件扩展名
	 *
	 * @return string
	 */
	protected function getFileExt()
	{
		return '.' . $this->file->guessExtension();
	}

	/**
	 * 重命名文件
	 *
	 * @return string
	 */
	protected function getFullName()
	{
		return mt_rand(1, 10000) . time() . $this->getFileExt();
	}

	/**
	 * 获取文件完整路径
	 *
	 * @return string
	 */
	protected function getFilePath()
	{
		return $this->config['pathFormat'] . date('Y_m_d') . '/';
	}

	/**
	 * 文件类型检测
	 *
	 * @return bool
	 */
	protected function checkType()
	{

		return in_array($this->getFileExt(), $this->config["allowFiles"]);
	}

	/**
	 * 获取当前上传成功文件的各项信息
	 *
	 * @return array
	 */
	public function getFileInfo()
	{
		return [
			"state"    => $this->stateInfo,
			"url"      => config('UEditorUpload.baseUrl') . $this->filePath . $this->fullName,
			"title"    => $this->fileName,
			"original" => $this->oriName,
			"type"     => $this->fileType,
			"size"     => $this->fileSize,
		];
	}
}