package com.bingetv.app.data.database;

import android.database.Cursor;
import androidx.lifecycle.LiveData;
import androidx.room.EntityDeletionOrUpdateAdapter;
import androidx.room.EntityInsertionAdapter;
import androidx.room.RoomDatabase;
import androidx.room.RoomSQLiteQuery;
import androidx.room.SharedSQLiteStatement;
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import java.lang.Class;
import java.lang.Exception;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.Callable;
import javax.annotation.processing.Generated;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class PlaylistDao_Impl implements PlaylistDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<PlaylistEntity> __insertionAdapterOfPlaylistEntity;

  private final EntityDeletionOrUpdateAdapter<PlaylistEntity> __deletionAdapterOfPlaylistEntity;

  private final EntityDeletionOrUpdateAdapter<PlaylistEntity> __updateAdapterOfPlaylistEntity;

  private final SharedSQLiteStatement __preparedStmtOfDeactivateAllPlaylists;

  private final SharedSQLiteStatement __preparedStmtOfActivatePlaylist;

  public PlaylistDao_Impl(RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfPlaylistEntity = new EntityInsertionAdapter<PlaylistEntity>(__db) {
      @Override
      public String createQuery() {
        return "INSERT OR REPLACE INTO `playlists` (`id`,`name`,`type`,`serverUrl`,`username`,`password`,`m3uUrl`,`isActive`,`lastSync`,`createdAt`) VALUES (nullif(?, 0),?,?,?,?,?,?,?,?,?)";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, PlaylistEntity value) {
        stmt.bindLong(1, value.getId());
        if (value.getName() == null) {
          stmt.bindNull(2);
        } else {
          stmt.bindString(2, value.getName());
        }
        if (value.getType() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getType());
        }
        if (value.getServerUrl() == null) {
          stmt.bindNull(4);
        } else {
          stmt.bindString(4, value.getServerUrl());
        }
        if (value.getUsername() == null) {
          stmt.bindNull(5);
        } else {
          stmt.bindString(5, value.getUsername());
        }
        if (value.getPassword() == null) {
          stmt.bindNull(6);
        } else {
          stmt.bindString(6, value.getPassword());
        }
        if (value.getM3uUrl() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getM3uUrl());
        }
        final int _tmp = value.isActive() ? 1 : 0;
        stmt.bindLong(8, _tmp);
        stmt.bindLong(9, value.getLastSync());
        stmt.bindLong(10, value.getCreatedAt());
      }
    };
    this.__deletionAdapterOfPlaylistEntity = new EntityDeletionOrUpdateAdapter<PlaylistEntity>(__db) {
      @Override
      public String createQuery() {
        return "DELETE FROM `playlists` WHERE `id` = ?";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, PlaylistEntity value) {
        stmt.bindLong(1, value.getId());
      }
    };
    this.__updateAdapterOfPlaylistEntity = new EntityDeletionOrUpdateAdapter<PlaylistEntity>(__db) {
      @Override
      public String createQuery() {
        return "UPDATE OR ABORT `playlists` SET `id` = ?,`name` = ?,`type` = ?,`serverUrl` = ?,`username` = ?,`password` = ?,`m3uUrl` = ?,`isActive` = ?,`lastSync` = ?,`createdAt` = ? WHERE `id` = ?";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, PlaylistEntity value) {
        stmt.bindLong(1, value.getId());
        if (value.getName() == null) {
          stmt.bindNull(2);
        } else {
          stmt.bindString(2, value.getName());
        }
        if (value.getType() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getType());
        }
        if (value.getServerUrl() == null) {
          stmt.bindNull(4);
        } else {
          stmt.bindString(4, value.getServerUrl());
        }
        if (value.getUsername() == null) {
          stmt.bindNull(5);
        } else {
          stmt.bindString(5, value.getUsername());
        }
        if (value.getPassword() == null) {
          stmt.bindNull(6);
        } else {
          stmt.bindString(6, value.getPassword());
        }
        if (value.getM3uUrl() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getM3uUrl());
        }
        final int _tmp = value.isActive() ? 1 : 0;
        stmt.bindLong(8, _tmp);
        stmt.bindLong(9, value.getLastSync());
        stmt.bindLong(10, value.getCreatedAt());
        stmt.bindLong(11, value.getId());
      }
    };
    this.__preparedStmtOfDeactivateAllPlaylists = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "UPDATE playlists SET isActive = 0";
        return _query;
      }
    };
    this.__preparedStmtOfActivatePlaylist = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "UPDATE playlists SET isActive = 1 WHERE id = ?";
        return _query;
      }
    };
  }

  @Override
  public long insertPlaylist(final PlaylistEntity playlist) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      long _result = __insertionAdapterOfPlaylistEntity.insertAndReturnId(playlist);
      __db.setTransactionSuccessful();
      return _result;
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void deletePlaylist(final PlaylistEntity playlist) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __deletionAdapterOfPlaylistEntity.handle(playlist);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void updatePlaylist(final PlaylistEntity playlist) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __updateAdapterOfPlaylistEntity.handle(playlist);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void deactivateAllPlaylists() {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfDeactivateAllPlaylists.acquire();
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfDeactivateAllPlaylists.release(_stmt);
    }
  }

  @Override
  public void activatePlaylist(final long playlistId) {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfActivatePlaylist.acquire();
    int _argIndex = 1;
    _stmt.bindLong(_argIndex, playlistId);
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfActivatePlaylist.release(_stmt);
    }
  }

  @Override
  public LiveData<List<PlaylistEntity>> getAllPlaylists() {
    final String _sql = "SELECT * FROM playlists ORDER BY createdAt DESC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return __db.getInvalidationTracker().createLiveData(new String[]{"playlists"}, false, new Callable<List<PlaylistEntity>>() {
      @Override
      public List<PlaylistEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
          final int _cursorIndexOfType = CursorUtil.getColumnIndexOrThrow(_cursor, "type");
          final int _cursorIndexOfServerUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "serverUrl");
          final int _cursorIndexOfUsername = CursorUtil.getColumnIndexOrThrow(_cursor, "username");
          final int _cursorIndexOfPassword = CursorUtil.getColumnIndexOrThrow(_cursor, "password");
          final int _cursorIndexOfM3uUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "m3uUrl");
          final int _cursorIndexOfIsActive = CursorUtil.getColumnIndexOrThrow(_cursor, "isActive");
          final int _cursorIndexOfLastSync = CursorUtil.getColumnIndexOrThrow(_cursor, "lastSync");
          final int _cursorIndexOfCreatedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "createdAt");
          final List<PlaylistEntity> _result = new ArrayList<PlaylistEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final PlaylistEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpName;
            if (_cursor.isNull(_cursorIndexOfName)) {
              _tmpName = null;
            } else {
              _tmpName = _cursor.getString(_cursorIndexOfName);
            }
            final String _tmpType;
            if (_cursor.isNull(_cursorIndexOfType)) {
              _tmpType = null;
            } else {
              _tmpType = _cursor.getString(_cursorIndexOfType);
            }
            final String _tmpServerUrl;
            if (_cursor.isNull(_cursorIndexOfServerUrl)) {
              _tmpServerUrl = null;
            } else {
              _tmpServerUrl = _cursor.getString(_cursorIndexOfServerUrl);
            }
            final String _tmpUsername;
            if (_cursor.isNull(_cursorIndexOfUsername)) {
              _tmpUsername = null;
            } else {
              _tmpUsername = _cursor.getString(_cursorIndexOfUsername);
            }
            final String _tmpPassword;
            if (_cursor.isNull(_cursorIndexOfPassword)) {
              _tmpPassword = null;
            } else {
              _tmpPassword = _cursor.getString(_cursorIndexOfPassword);
            }
            final String _tmpM3uUrl;
            if (_cursor.isNull(_cursorIndexOfM3uUrl)) {
              _tmpM3uUrl = null;
            } else {
              _tmpM3uUrl = _cursor.getString(_cursorIndexOfM3uUrl);
            }
            final boolean _tmpIsActive;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsActive);
            _tmpIsActive = _tmp != 0;
            final long _tmpLastSync;
            _tmpLastSync = _cursor.getLong(_cursorIndexOfLastSync);
            final long _tmpCreatedAt;
            _tmpCreatedAt = _cursor.getLong(_cursorIndexOfCreatedAt);
            _item = new PlaylistEntity(_tmpId,_tmpName,_tmpType,_tmpServerUrl,_tmpUsername,_tmpPassword,_tmpM3uUrl,_tmpIsActive,_tmpLastSync,_tmpCreatedAt);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
        }
      }

      @Override
      protected void finalize() {
        _statement.release();
      }
    });
  }

  @Override
  public PlaylistEntity getActivePlaylist() {
    final String _sql = "SELECT * FROM playlists WHERE isActive = 1 LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    __db.assertNotSuspendingTransaction();
    final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
    try {
      final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
      final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
      final int _cursorIndexOfType = CursorUtil.getColumnIndexOrThrow(_cursor, "type");
      final int _cursorIndexOfServerUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "serverUrl");
      final int _cursorIndexOfUsername = CursorUtil.getColumnIndexOrThrow(_cursor, "username");
      final int _cursorIndexOfPassword = CursorUtil.getColumnIndexOrThrow(_cursor, "password");
      final int _cursorIndexOfM3uUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "m3uUrl");
      final int _cursorIndexOfIsActive = CursorUtil.getColumnIndexOrThrow(_cursor, "isActive");
      final int _cursorIndexOfLastSync = CursorUtil.getColumnIndexOrThrow(_cursor, "lastSync");
      final int _cursorIndexOfCreatedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "createdAt");
      final PlaylistEntity _result;
      if(_cursor.moveToFirst()) {
        final long _tmpId;
        _tmpId = _cursor.getLong(_cursorIndexOfId);
        final String _tmpName;
        if (_cursor.isNull(_cursorIndexOfName)) {
          _tmpName = null;
        } else {
          _tmpName = _cursor.getString(_cursorIndexOfName);
        }
        final String _tmpType;
        if (_cursor.isNull(_cursorIndexOfType)) {
          _tmpType = null;
        } else {
          _tmpType = _cursor.getString(_cursorIndexOfType);
        }
        final String _tmpServerUrl;
        if (_cursor.isNull(_cursorIndexOfServerUrl)) {
          _tmpServerUrl = null;
        } else {
          _tmpServerUrl = _cursor.getString(_cursorIndexOfServerUrl);
        }
        final String _tmpUsername;
        if (_cursor.isNull(_cursorIndexOfUsername)) {
          _tmpUsername = null;
        } else {
          _tmpUsername = _cursor.getString(_cursorIndexOfUsername);
        }
        final String _tmpPassword;
        if (_cursor.isNull(_cursorIndexOfPassword)) {
          _tmpPassword = null;
        } else {
          _tmpPassword = _cursor.getString(_cursorIndexOfPassword);
        }
        final String _tmpM3uUrl;
        if (_cursor.isNull(_cursorIndexOfM3uUrl)) {
          _tmpM3uUrl = null;
        } else {
          _tmpM3uUrl = _cursor.getString(_cursorIndexOfM3uUrl);
        }
        final boolean _tmpIsActive;
        final int _tmp;
        _tmp = _cursor.getInt(_cursorIndexOfIsActive);
        _tmpIsActive = _tmp != 0;
        final long _tmpLastSync;
        _tmpLastSync = _cursor.getLong(_cursorIndexOfLastSync);
        final long _tmpCreatedAt;
        _tmpCreatedAt = _cursor.getLong(_cursorIndexOfCreatedAt);
        _result = new PlaylistEntity(_tmpId,_tmpName,_tmpType,_tmpServerUrl,_tmpUsername,_tmpPassword,_tmpM3uUrl,_tmpIsActive,_tmpLastSync,_tmpCreatedAt);
      } else {
        _result = null;
      }
      return _result;
    } finally {
      _cursor.close();
      _statement.release();
    }
  }

  public static List<Class<?>> getRequiredConverters() {
    return Collections.emptyList();
  }
}
