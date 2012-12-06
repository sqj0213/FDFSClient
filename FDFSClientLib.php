<?php
/* 公有上传,请先运行:publicFDFSClient::info()
 * 私有上传,请先运行:privateFDFSClient::info()
 * FastDFS php client lib
 * 依赖 FastDFS-php-client-3.11-1.x86_64
 * module的配置文件分：/etc/php.d/fastdfs_client.ini 
 * fastdfs_client.tracker_group0 = /etc/fdfs/client.conf
 * fastdfs_client.tracker_group1 = /etc/fdfs/client-space.conf
 * 查看是存储服务是否正常请调用：publicFDFSClient::info()与privateFDFSClient::info()两个方法来验证即可
 */

//0为fdfs集群0,此存储的访问无需权限验证
//对应的配置文件：fastdfs_client.tracker_group0 = /etc/fdfs/client.conf
define( "FASTDFS_PUBLIC_CLUSTER", 0 );
//1为fdfs集群1,此存储的访问需要权限验证
//对应的配置文件:fastdfs_client.tracker_group1 = /etc/fdfs/client-space.conf
define( "FASTDFS_PRIVATE_CLUSTER", 1 );
//fdfs fileID 长度
define( 'FDFSFILEIDMINLEN', 32 );

/*
 * FDFSClient
 */
class FDFSClient{
    
    function uploadByFileName( $uploadFilePath="", $typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        $retData = array();
        $FDFSHaldler = new FastDFS( $typeFlag );
        $fileInfo = $FDFSHaldler->storage_upload_by_filename1( $uploadFilePath );
        if ( $fileInfo ) 
             $retData = array('retCode'=>1,'errMsg'=>'success', 'data'=>$fileInfo );
        else
            $retData = array('retCode'=>$FDFSHaldler->get_last_error_no(),'errMsg'=>$FDFSHaldler->get_last_error_info(),'data'=>'');
        $FDFSHaldler->tracker_close_all_connections();
        return retData;
    }
    
    function uploadSlaveFile( $masterFileID='', $slaveFileName='', $typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        $retData = array();
        $FDFSHaldler = new FastDFS( $typeFlag );
        $slaveFileID = $fdfs->storage_upload_slave_by_filename1( $slaveFileName, $masterFileID, NULL, NULL );
        if ( $slaveFileID )
            $retData = array( 'retCode'=>1, 'errMsg'=>'sucess', 'data'=>$slaveFileID );
        else
            $retData = array( 'retCode'=>$FDFSHaldler->get_last_error_no(), 'errMsg'=>$FDFSHaldler->get_last_error_info(), 'data'=>$slaveFileName );
        $FDFSHaldler->tracker_close_all_connections();
        return $retData;
    }
    
    function deleteFile( $fileID="", $typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        $retData = array();
        if ( Empty( $fileID ) || len( $fileID )< FDFSFILEIDMINLEN )
        {
            $retData = array('retCode'=>-1,'errMsg'=>'fildID invalid', 'data'=>$fileID );  
            return $retData;
        }
        $FDFSHaldler = new FastDFS( $typeFlag );
        $fileInfo = $FDFSHaldler->storage_delete_file1( $fileID );
        if ( $fileInfo ) 
             $retData = array('retCode'=>1,'errMsg'=>'success', 'data'=>$fileInfo );
        else
            $retData = array('retCode'=>get_last_error_no(),'errMsg'=>get_last_error_info(),'data'=>'');
        $FDFSHaldler->tracker_close_all_connections();
        return retData;
    }
    
    function multiDeleteFile( $fileIDArray=array(), $typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        $FDFSHaldler = new FastDFS($typeFlag);
        $retData = array();
        $errorData = array();
        $successData = array();
        $len = count( $fileIDArray );
        $errorFlag = False;
        for( $i = 0; $i < $len; $i++ )
        {
            $fileInfo = $FDFSHaldler->storage_delete_file1( $fileIDArray[ $i ] );
            if ( $fileInfo ) 
                 $successData = array_push( $successData, array('retCode'=>1,'errMsg'=>'success', 'para'=>$fileIDArray[ $i ], 'data'=>$fileInfo ) );
            else
            {
                $errorFlag = True;
                $successData = array_push( $successData, array('retCode'=>$FDFSHaldler->get_last_error_no(),'errMsg'=>$FDFSHaldler->get_last_error_info(),'para'=>$fileIDArray[ $i ],'data'=>'' ) );            
            }
        }
        if ( !$errorFlag )
        {
            $retData = array( 'retCode'=>1, 'errMsg'=>'success', 'data'=>$successData );  
        }
        else 
            $retData = array( 'retCode'=>0, 'errMsg'=>'error', 'data'=>$successData );

        $FDFSHaldler->tracker_close_all_connections();
        return $retData;
    }
    function multiUploadByFileNameArray($fileNameArray=array(), $type=FASTDFS_PUBLIC_CLUSTER )
    {
        $FDFSHaldler = new FastDFS($typeFlag);
        $retData = array();
        $errorData = array();
        $successData = array();
        $len = count( $fileNameArray );
        $errorFlag = False;
        for( $i = 0; $i < $len; $i++ )
        {
            $fileInfo = $FDFSHaldler->storage_upload_by_filename1( $fileNameArray[ $i ] );
            if ( $fileInfo ) 
                 $successData = array_push( $successData, array('retCode'=>1,'errMsg'=>'success', 'para'=>$fileNameArray[ $i ], 'data'=>$fileInfo ) );
            else
            {
                $errorFlag = True;
                $successData = array_push( $successData, array('retCode'=>$FDFSHaldler->get_last_error_no(),'errMsg'=>$FDFSHaldler->get_last_error_info(),'para'=>$fileNameArray[ $i ],'data'=>'' ) );            
            }
        }
        if ( !$errorFlag )
        {
            $retData = array( 'retCode'=>1, 'errMsg'=>'success', 'data'=>$successData );  
        }
        else 
            $retData = array( 'retCode'=>0, 'errMsg'=>'error', 'data'=>$successData );

        $FDFSHaldler->tracker_close_all_connections();
        return $retData;
    }
    function info( $typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        $FDFSHaldler = new FastDFS($typeFlag);
        $tracker = $FDFSHaldler->tracker_get_connection();
        var_dump($tracker);
        $server = $FDFSHaldler->connect_server($tracker['ip_addr'], $tracker['port']);
        var_dump($server);
        var_dump($FDFSHaldler->disconnect_server($server));
        var_dump($FDFSHaldler->tracker_query_storage_store_list());
        var_dump($FDFSHaldler->active_test($tracker));
        $FDFSHaldler->tracker_close_all_connections();   
    }
}
/*
 * 公有存储对外接口
 */
class publicFDFSClient
{
    function uploadByFileNameArray( $fileNameArray=array('/usr/include/stdio.h','/usr/include/stdlib.h') )
    {
        return FDFSClient::multiUploadByFileNameArray( $fileNameArray, FASTDFS_PUBLIC_CLUSTER );
    }
    function uploadByFileName( $uploadFilePath="" )
    {
        return FDFSClient::uploadByFileName( $uploadFilePath, FASTDFS_PUBLIC_CLUSTER );
    }
    function uploadSlaveFile( $masterFileID='', $slaveFileName='' )
    {
        return FDFSClient::uploadByFileName( $uploadFilePath, FASTDFS_PUBLIC_CLUSTER );
    }
    function multiDeleteFile( $fileIDArray=array('xxxx','xxxx') )
    {
        return FDFSClient::multiDeleteFile( $fileIDArray, FASTDFS_PUBLIC_CLUSTER );
    }
    function deleteFile( $fileID="" )
    {
        return FDFSClient::deleteFile( $fileID, FASTDFS_PUBLIC_CLUSTER );
    }

    function info($typeFlag = FASTDFS_PUBLIC_CLUSTER )
    {
        FDFSClient::info( FASTDFS_PUBLIC_CLUSTER );
    }
}

/*
 * 私有需验证访问的对外接口
 */
class privateFDFSClient
{

    function uploadByFileNameArray( $fileNameArray=array('/usr/include/stdio.h','/usr/include/stdlib.h') )
    {
        return FDFSClient::multiUploadByFileNameArray( $fileNameArray, FASTDFS_PRIVATE_CLUSTER );
    }
    function uploadByFileName( $uploadFilePath="" )
    {
        return FDFSClient::uploadByFileName( $uploadFilePath, FASTDFS_PRIVATE_CLUSTER );
    }
    function multiDeleteFile( $fileIDArray=array('xxxx','xxxx') )
    {
        return FDFSClient::multiDeleteFile( $fileIDArray, FASTDFS_PRIVATE_CLUSTER );
    }
    function uploadSlaveFile( $masterFileID='', $slaveFileName='' )
    {
        return FDFSClient::uploadByFileName( $uploadFilePath, FASTDFS_PRIVATE_CLUSTER );
    }
    function deleteFile( $fileID="" )
    {
        return FDFSClient::deleteFile( $fileID, FASTDFS_PRIVATE_CLUSTER );
    }
    function info()
    {
        FDFSClient::info( FASTDFS_PRIVATE_CLUSTER );
    }
}
?>

