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
public final class ChannelDao_Impl implements ChannelDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<ChannelEntity> __insertionAdapterOfChannelEntity;

  private final EntityDeletionOrUpdateAdapter<ChannelEntity> __deletionAdapterOfChannelEntity;

  private final EntityDeletionOrUpdateAdapter<ChannelEntity> __updateAdapterOfChannelEntity;

  private final SharedSQLiteStatement __preparedStmtOfDeleteAllChannels;

  private final SharedSQLiteStatement __preparedStmtOfUpdateFavoriteStatus;

  public ChannelDao_Impl(RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfChannelEntity = new EntityInsertionAdapter<ChannelEntity>(__db) {
      @Override
      public String createQuery() {
        return "INSERT OR REPLACE INTO `channels` (`id`,`streamId`,`name`,`streamUrl`,`logoUrl`,`category`,`categoryId`,`tvgId`,`tvgName`,`tvgLogo`,`tvgChno`,`epgChannelId`,`isFavorite`,`isLocked`,`sortOrder`,`addedAt`) VALUES (nullif(?, 0),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, ChannelEntity value) {
        stmt.bindLong(1, value.getId());
        if (value.getStreamId() == null) {
          stmt.bindNull(2);
        } else {
          stmt.bindString(2, value.getStreamId());
        }
        if (value.getName() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getName());
        }
        if (value.getStreamUrl() == null) {
          stmt.bindNull(4);
        } else {
          stmt.bindString(4, value.getStreamUrl());
        }
        if (value.getLogoUrl() == null) {
          stmt.bindNull(5);
        } else {
          stmt.bindString(5, value.getLogoUrl());
        }
        if (value.getCategory() == null) {
          stmt.bindNull(6);
        } else {
          stmt.bindString(6, value.getCategory());
        }
        if (value.getCategoryId() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getCategoryId());
        }
        if (value.getTvgId() == null) {
          stmt.bindNull(8);
        } else {
          stmt.bindString(8, value.getTvgId());
        }
        if (value.getTvgName() == null) {
          stmt.bindNull(9);
        } else {
          stmt.bindString(9, value.getTvgName());
        }
        if (value.getTvgLogo() == null) {
          stmt.bindNull(10);
        } else {
          stmt.bindString(10, value.getTvgLogo());
        }
        if (value.getTvgChno() == null) {
          stmt.bindNull(11);
        } else {
          stmt.bindString(11, value.getTvgChno());
        }
        if (value.getEpgChannelId() == null) {
          stmt.bindNull(12);
        } else {
          stmt.bindString(12, value.getEpgChannelId());
        }
        final int _tmp = value.isFavorite() ? 1 : 0;
        stmt.bindLong(13, _tmp);
        final int _tmp_1 = value.isLocked() ? 1 : 0;
        stmt.bindLong(14, _tmp_1);
        stmt.bindLong(15, value.getSortOrder());
        stmt.bindLong(16, value.getAddedAt());
      }
    };
    this.__deletionAdapterOfChannelEntity = new EntityDeletionOrUpdateAdapter<ChannelEntity>(__db) {
      @Override
      public String createQuery() {
        return "DELETE FROM `channels` WHERE `id` = ?";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, ChannelEntity value) {
        stmt.bindLong(1, value.getId());
      }
    };
    this.__updateAdapterOfChannelEntity = new EntityDeletionOrUpdateAdapter<ChannelEntity>(__db) {
      @Override
      public String createQuery() {
        return "UPDATE OR ABORT `channels` SET `id` = ?,`streamId` = ?,`name` = ?,`streamUrl` = ?,`logoUrl` = ?,`category` = ?,`categoryId` = ?,`tvgId` = ?,`tvgName` = ?,`tvgLogo` = ?,`tvgChno` = ?,`epgChannelId` = ?,`isFavorite` = ?,`isLocked` = ?,`sortOrder` = ?,`addedAt` = ? WHERE `id` = ?";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, ChannelEntity value) {
        stmt.bindLong(1, value.getId());
        if (value.getStreamId() == null) {
          stmt.bindNull(2);
        } else {
          stmt.bindString(2, value.getStreamId());
        }
        if (value.getName() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getName());
        }
        if (value.getStreamUrl() == null) {
          stmt.bindNull(4);
        } else {
          stmt.bindString(4, value.getStreamUrl());
        }
        if (value.getLogoUrl() == null) {
          stmt.bindNull(5);
        } else {
          stmt.bindString(5, value.getLogoUrl());
        }
        if (value.getCategory() == null) {
          stmt.bindNull(6);
        } else {
          stmt.bindString(6, value.getCategory());
        }
        if (value.getCategoryId() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getCategoryId());
        }
        if (value.getTvgId() == null) {
          stmt.bindNull(8);
        } else {
          stmt.bindString(8, value.getTvgId());
        }
        if (value.getTvgName() == null) {
          stmt.bindNull(9);
        } else {
          stmt.bindString(9, value.getTvgName());
        }
        if (value.getTvgLogo() == null) {
          stmt.bindNull(10);
        } else {
          stmt.bindString(10, value.getTvgLogo());
        }
        if (value.getTvgChno() == null) {
          stmt.bindNull(11);
        } else {
          stmt.bindString(11, value.getTvgChno());
        }
        if (value.getEpgChannelId() == null) {
          stmt.bindNull(12);
        } else {
          stmt.bindString(12, value.getEpgChannelId());
        }
        final int _tmp = value.isFavorite() ? 1 : 0;
        stmt.bindLong(13, _tmp);
        final int _tmp_1 = value.isLocked() ? 1 : 0;
        stmt.bindLong(14, _tmp_1);
        stmt.bindLong(15, value.getSortOrder());
        stmt.bindLong(16, value.getAddedAt());
        stmt.bindLong(17, value.getId());
      }
    };
    this.__preparedStmtOfDeleteAllChannels = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "DELETE FROM channels";
        return _query;
      }
    };
    this.__preparedStmtOfUpdateFavoriteStatus = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "UPDATE channels SET isFavorite = ? WHERE id = ?";
        return _query;
      }
    };
  }

  @Override
  public long insertChannel(final ChannelEntity channel) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      long _result = __insertionAdapterOfChannelEntity.insertAndReturnId(channel);
      __db.setTransactionSuccessful();
      return _result;
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void insertChannels(final List<ChannelEntity> channels) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __insertionAdapterOfChannelEntity.insert(channels);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void deleteChannel(final ChannelEntity channel) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __deletionAdapterOfChannelEntity.handle(channel);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void updateChannel(final ChannelEntity channel) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __updateAdapterOfChannelEntity.handle(channel);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void deleteAllChannels() {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfDeleteAllChannels.acquire();
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfDeleteAllChannels.release(_stmt);
    }
  }

  @Override
  public void updateFavoriteStatus(final long channelId, final boolean isFavorite) {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfUpdateFavoriteStatus.acquire();
    int _argIndex = 1;
    final int _tmp = isFavorite ? 1 : 0;
    _stmt.bindLong(_argIndex, _tmp);
    _argIndex = 2;
    _stmt.bindLong(_argIndex, channelId);
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfUpdateFavoriteStatus.release(_stmt);
    }
  }

  @Override
  public LiveData<List<ChannelEntity>> getAllChannels() {
    final String _sql = "SELECT * FROM channels ORDER BY sortOrder ASC, name ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return __db.getInvalidationTracker().createLiveData(new String[]{"channels"}, false, new Callable<List<ChannelEntity>>() {
      @Override
      public List<ChannelEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfStreamId = CursorUtil.getColumnIndexOrThrow(_cursor, "streamId");
          final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
          final int _cursorIndexOfStreamUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "streamUrl");
          final int _cursorIndexOfLogoUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "logoUrl");
          final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
          final int _cursorIndexOfCategoryId = CursorUtil.getColumnIndexOrThrow(_cursor, "categoryId");
          final int _cursorIndexOfTvgId = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgId");
          final int _cursorIndexOfTvgName = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgName");
          final int _cursorIndexOfTvgLogo = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgLogo");
          final int _cursorIndexOfTvgChno = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgChno");
          final int _cursorIndexOfEpgChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "epgChannelId");
          final int _cursorIndexOfIsFavorite = CursorUtil.getColumnIndexOrThrow(_cursor, "isFavorite");
          final int _cursorIndexOfIsLocked = CursorUtil.getColumnIndexOrThrow(_cursor, "isLocked");
          final int _cursorIndexOfSortOrder = CursorUtil.getColumnIndexOrThrow(_cursor, "sortOrder");
          final int _cursorIndexOfAddedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "addedAt");
          final List<ChannelEntity> _result = new ArrayList<ChannelEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final ChannelEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpStreamId;
            if (_cursor.isNull(_cursorIndexOfStreamId)) {
              _tmpStreamId = null;
            } else {
              _tmpStreamId = _cursor.getString(_cursorIndexOfStreamId);
            }
            final String _tmpName;
            if (_cursor.isNull(_cursorIndexOfName)) {
              _tmpName = null;
            } else {
              _tmpName = _cursor.getString(_cursorIndexOfName);
            }
            final String _tmpStreamUrl;
            if (_cursor.isNull(_cursorIndexOfStreamUrl)) {
              _tmpStreamUrl = null;
            } else {
              _tmpStreamUrl = _cursor.getString(_cursorIndexOfStreamUrl);
            }
            final String _tmpLogoUrl;
            if (_cursor.isNull(_cursorIndexOfLogoUrl)) {
              _tmpLogoUrl = null;
            } else {
              _tmpLogoUrl = _cursor.getString(_cursorIndexOfLogoUrl);
            }
            final String _tmpCategory;
            if (_cursor.isNull(_cursorIndexOfCategory)) {
              _tmpCategory = null;
            } else {
              _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
            }
            final String _tmpCategoryId;
            if (_cursor.isNull(_cursorIndexOfCategoryId)) {
              _tmpCategoryId = null;
            } else {
              _tmpCategoryId = _cursor.getString(_cursorIndexOfCategoryId);
            }
            final String _tmpTvgId;
            if (_cursor.isNull(_cursorIndexOfTvgId)) {
              _tmpTvgId = null;
            } else {
              _tmpTvgId = _cursor.getString(_cursorIndexOfTvgId);
            }
            final String _tmpTvgName;
            if (_cursor.isNull(_cursorIndexOfTvgName)) {
              _tmpTvgName = null;
            } else {
              _tmpTvgName = _cursor.getString(_cursorIndexOfTvgName);
            }
            final String _tmpTvgLogo;
            if (_cursor.isNull(_cursorIndexOfTvgLogo)) {
              _tmpTvgLogo = null;
            } else {
              _tmpTvgLogo = _cursor.getString(_cursorIndexOfTvgLogo);
            }
            final String _tmpTvgChno;
            if (_cursor.isNull(_cursorIndexOfTvgChno)) {
              _tmpTvgChno = null;
            } else {
              _tmpTvgChno = _cursor.getString(_cursorIndexOfTvgChno);
            }
            final String _tmpEpgChannelId;
            if (_cursor.isNull(_cursorIndexOfEpgChannelId)) {
              _tmpEpgChannelId = null;
            } else {
              _tmpEpgChannelId = _cursor.getString(_cursorIndexOfEpgChannelId);
            }
            final boolean _tmpIsFavorite;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsFavorite);
            _tmpIsFavorite = _tmp != 0;
            final boolean _tmpIsLocked;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsLocked);
            _tmpIsLocked = _tmp_1 != 0;
            final int _tmpSortOrder;
            _tmpSortOrder = _cursor.getInt(_cursorIndexOfSortOrder);
            final long _tmpAddedAt;
            _tmpAddedAt = _cursor.getLong(_cursorIndexOfAddedAt);
            _item = new ChannelEntity(_tmpId,_tmpStreamId,_tmpName,_tmpStreamUrl,_tmpLogoUrl,_tmpCategory,_tmpCategoryId,_tmpTvgId,_tmpTvgName,_tmpTvgLogo,_tmpTvgChno,_tmpEpgChannelId,_tmpIsFavorite,_tmpIsLocked,_tmpSortOrder,_tmpAddedAt);
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
  public LiveData<List<ChannelEntity>> getChannelsByCategory(final String category) {
    final String _sql = "SELECT * FROM channels WHERE category = ? ORDER BY sortOrder ASC, name ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    if (category == null) {
      _statement.bindNull(_argIndex);
    } else {
      _statement.bindString(_argIndex, category);
    }
    return __db.getInvalidationTracker().createLiveData(new String[]{"channels"}, false, new Callable<List<ChannelEntity>>() {
      @Override
      public List<ChannelEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfStreamId = CursorUtil.getColumnIndexOrThrow(_cursor, "streamId");
          final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
          final int _cursorIndexOfStreamUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "streamUrl");
          final int _cursorIndexOfLogoUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "logoUrl");
          final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
          final int _cursorIndexOfCategoryId = CursorUtil.getColumnIndexOrThrow(_cursor, "categoryId");
          final int _cursorIndexOfTvgId = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgId");
          final int _cursorIndexOfTvgName = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgName");
          final int _cursorIndexOfTvgLogo = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgLogo");
          final int _cursorIndexOfTvgChno = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgChno");
          final int _cursorIndexOfEpgChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "epgChannelId");
          final int _cursorIndexOfIsFavorite = CursorUtil.getColumnIndexOrThrow(_cursor, "isFavorite");
          final int _cursorIndexOfIsLocked = CursorUtil.getColumnIndexOrThrow(_cursor, "isLocked");
          final int _cursorIndexOfSortOrder = CursorUtil.getColumnIndexOrThrow(_cursor, "sortOrder");
          final int _cursorIndexOfAddedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "addedAt");
          final List<ChannelEntity> _result = new ArrayList<ChannelEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final ChannelEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpStreamId;
            if (_cursor.isNull(_cursorIndexOfStreamId)) {
              _tmpStreamId = null;
            } else {
              _tmpStreamId = _cursor.getString(_cursorIndexOfStreamId);
            }
            final String _tmpName;
            if (_cursor.isNull(_cursorIndexOfName)) {
              _tmpName = null;
            } else {
              _tmpName = _cursor.getString(_cursorIndexOfName);
            }
            final String _tmpStreamUrl;
            if (_cursor.isNull(_cursorIndexOfStreamUrl)) {
              _tmpStreamUrl = null;
            } else {
              _tmpStreamUrl = _cursor.getString(_cursorIndexOfStreamUrl);
            }
            final String _tmpLogoUrl;
            if (_cursor.isNull(_cursorIndexOfLogoUrl)) {
              _tmpLogoUrl = null;
            } else {
              _tmpLogoUrl = _cursor.getString(_cursorIndexOfLogoUrl);
            }
            final String _tmpCategory;
            if (_cursor.isNull(_cursorIndexOfCategory)) {
              _tmpCategory = null;
            } else {
              _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
            }
            final String _tmpCategoryId;
            if (_cursor.isNull(_cursorIndexOfCategoryId)) {
              _tmpCategoryId = null;
            } else {
              _tmpCategoryId = _cursor.getString(_cursorIndexOfCategoryId);
            }
            final String _tmpTvgId;
            if (_cursor.isNull(_cursorIndexOfTvgId)) {
              _tmpTvgId = null;
            } else {
              _tmpTvgId = _cursor.getString(_cursorIndexOfTvgId);
            }
            final String _tmpTvgName;
            if (_cursor.isNull(_cursorIndexOfTvgName)) {
              _tmpTvgName = null;
            } else {
              _tmpTvgName = _cursor.getString(_cursorIndexOfTvgName);
            }
            final String _tmpTvgLogo;
            if (_cursor.isNull(_cursorIndexOfTvgLogo)) {
              _tmpTvgLogo = null;
            } else {
              _tmpTvgLogo = _cursor.getString(_cursorIndexOfTvgLogo);
            }
            final String _tmpTvgChno;
            if (_cursor.isNull(_cursorIndexOfTvgChno)) {
              _tmpTvgChno = null;
            } else {
              _tmpTvgChno = _cursor.getString(_cursorIndexOfTvgChno);
            }
            final String _tmpEpgChannelId;
            if (_cursor.isNull(_cursorIndexOfEpgChannelId)) {
              _tmpEpgChannelId = null;
            } else {
              _tmpEpgChannelId = _cursor.getString(_cursorIndexOfEpgChannelId);
            }
            final boolean _tmpIsFavorite;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsFavorite);
            _tmpIsFavorite = _tmp != 0;
            final boolean _tmpIsLocked;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsLocked);
            _tmpIsLocked = _tmp_1 != 0;
            final int _tmpSortOrder;
            _tmpSortOrder = _cursor.getInt(_cursorIndexOfSortOrder);
            final long _tmpAddedAt;
            _tmpAddedAt = _cursor.getLong(_cursorIndexOfAddedAt);
            _item = new ChannelEntity(_tmpId,_tmpStreamId,_tmpName,_tmpStreamUrl,_tmpLogoUrl,_tmpCategory,_tmpCategoryId,_tmpTvgId,_tmpTvgName,_tmpTvgLogo,_tmpTvgChno,_tmpEpgChannelId,_tmpIsFavorite,_tmpIsLocked,_tmpSortOrder,_tmpAddedAt);
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
  public LiveData<List<ChannelEntity>> getFavoriteChannels() {
    final String _sql = "SELECT * FROM channels WHERE isFavorite = 1 ORDER BY sortOrder ASC, name ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return __db.getInvalidationTracker().createLiveData(new String[]{"channels"}, false, new Callable<List<ChannelEntity>>() {
      @Override
      public List<ChannelEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfStreamId = CursorUtil.getColumnIndexOrThrow(_cursor, "streamId");
          final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
          final int _cursorIndexOfStreamUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "streamUrl");
          final int _cursorIndexOfLogoUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "logoUrl");
          final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
          final int _cursorIndexOfCategoryId = CursorUtil.getColumnIndexOrThrow(_cursor, "categoryId");
          final int _cursorIndexOfTvgId = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgId");
          final int _cursorIndexOfTvgName = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgName");
          final int _cursorIndexOfTvgLogo = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgLogo");
          final int _cursorIndexOfTvgChno = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgChno");
          final int _cursorIndexOfEpgChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "epgChannelId");
          final int _cursorIndexOfIsFavorite = CursorUtil.getColumnIndexOrThrow(_cursor, "isFavorite");
          final int _cursorIndexOfIsLocked = CursorUtil.getColumnIndexOrThrow(_cursor, "isLocked");
          final int _cursorIndexOfSortOrder = CursorUtil.getColumnIndexOrThrow(_cursor, "sortOrder");
          final int _cursorIndexOfAddedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "addedAt");
          final List<ChannelEntity> _result = new ArrayList<ChannelEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final ChannelEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpStreamId;
            if (_cursor.isNull(_cursorIndexOfStreamId)) {
              _tmpStreamId = null;
            } else {
              _tmpStreamId = _cursor.getString(_cursorIndexOfStreamId);
            }
            final String _tmpName;
            if (_cursor.isNull(_cursorIndexOfName)) {
              _tmpName = null;
            } else {
              _tmpName = _cursor.getString(_cursorIndexOfName);
            }
            final String _tmpStreamUrl;
            if (_cursor.isNull(_cursorIndexOfStreamUrl)) {
              _tmpStreamUrl = null;
            } else {
              _tmpStreamUrl = _cursor.getString(_cursorIndexOfStreamUrl);
            }
            final String _tmpLogoUrl;
            if (_cursor.isNull(_cursorIndexOfLogoUrl)) {
              _tmpLogoUrl = null;
            } else {
              _tmpLogoUrl = _cursor.getString(_cursorIndexOfLogoUrl);
            }
            final String _tmpCategory;
            if (_cursor.isNull(_cursorIndexOfCategory)) {
              _tmpCategory = null;
            } else {
              _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
            }
            final String _tmpCategoryId;
            if (_cursor.isNull(_cursorIndexOfCategoryId)) {
              _tmpCategoryId = null;
            } else {
              _tmpCategoryId = _cursor.getString(_cursorIndexOfCategoryId);
            }
            final String _tmpTvgId;
            if (_cursor.isNull(_cursorIndexOfTvgId)) {
              _tmpTvgId = null;
            } else {
              _tmpTvgId = _cursor.getString(_cursorIndexOfTvgId);
            }
            final String _tmpTvgName;
            if (_cursor.isNull(_cursorIndexOfTvgName)) {
              _tmpTvgName = null;
            } else {
              _tmpTvgName = _cursor.getString(_cursorIndexOfTvgName);
            }
            final String _tmpTvgLogo;
            if (_cursor.isNull(_cursorIndexOfTvgLogo)) {
              _tmpTvgLogo = null;
            } else {
              _tmpTvgLogo = _cursor.getString(_cursorIndexOfTvgLogo);
            }
            final String _tmpTvgChno;
            if (_cursor.isNull(_cursorIndexOfTvgChno)) {
              _tmpTvgChno = null;
            } else {
              _tmpTvgChno = _cursor.getString(_cursorIndexOfTvgChno);
            }
            final String _tmpEpgChannelId;
            if (_cursor.isNull(_cursorIndexOfEpgChannelId)) {
              _tmpEpgChannelId = null;
            } else {
              _tmpEpgChannelId = _cursor.getString(_cursorIndexOfEpgChannelId);
            }
            final boolean _tmpIsFavorite;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsFavorite);
            _tmpIsFavorite = _tmp != 0;
            final boolean _tmpIsLocked;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsLocked);
            _tmpIsLocked = _tmp_1 != 0;
            final int _tmpSortOrder;
            _tmpSortOrder = _cursor.getInt(_cursorIndexOfSortOrder);
            final long _tmpAddedAt;
            _tmpAddedAt = _cursor.getLong(_cursorIndexOfAddedAt);
            _item = new ChannelEntity(_tmpId,_tmpStreamId,_tmpName,_tmpStreamUrl,_tmpLogoUrl,_tmpCategory,_tmpCategoryId,_tmpTvgId,_tmpTvgName,_tmpTvgLogo,_tmpTvgChno,_tmpEpgChannelId,_tmpIsFavorite,_tmpIsLocked,_tmpSortOrder,_tmpAddedAt);
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
  public LiveData<List<ChannelEntity>> searchChannels(final String query) {
    final String _sql = "SELECT * FROM channels WHERE name LIKE '%' || ? || '%' OR category LIKE '%' || ? || '%'";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 2);
    int _argIndex = 1;
    if (query == null) {
      _statement.bindNull(_argIndex);
    } else {
      _statement.bindString(_argIndex, query);
    }
    _argIndex = 2;
    if (query == null) {
      _statement.bindNull(_argIndex);
    } else {
      _statement.bindString(_argIndex, query);
    }
    return __db.getInvalidationTracker().createLiveData(new String[]{"channels"}, false, new Callable<List<ChannelEntity>>() {
      @Override
      public List<ChannelEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfStreamId = CursorUtil.getColumnIndexOrThrow(_cursor, "streamId");
          final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
          final int _cursorIndexOfStreamUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "streamUrl");
          final int _cursorIndexOfLogoUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "logoUrl");
          final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
          final int _cursorIndexOfCategoryId = CursorUtil.getColumnIndexOrThrow(_cursor, "categoryId");
          final int _cursorIndexOfTvgId = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgId");
          final int _cursorIndexOfTvgName = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgName");
          final int _cursorIndexOfTvgLogo = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgLogo");
          final int _cursorIndexOfTvgChno = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgChno");
          final int _cursorIndexOfEpgChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "epgChannelId");
          final int _cursorIndexOfIsFavorite = CursorUtil.getColumnIndexOrThrow(_cursor, "isFavorite");
          final int _cursorIndexOfIsLocked = CursorUtil.getColumnIndexOrThrow(_cursor, "isLocked");
          final int _cursorIndexOfSortOrder = CursorUtil.getColumnIndexOrThrow(_cursor, "sortOrder");
          final int _cursorIndexOfAddedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "addedAt");
          final List<ChannelEntity> _result = new ArrayList<ChannelEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final ChannelEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpStreamId;
            if (_cursor.isNull(_cursorIndexOfStreamId)) {
              _tmpStreamId = null;
            } else {
              _tmpStreamId = _cursor.getString(_cursorIndexOfStreamId);
            }
            final String _tmpName;
            if (_cursor.isNull(_cursorIndexOfName)) {
              _tmpName = null;
            } else {
              _tmpName = _cursor.getString(_cursorIndexOfName);
            }
            final String _tmpStreamUrl;
            if (_cursor.isNull(_cursorIndexOfStreamUrl)) {
              _tmpStreamUrl = null;
            } else {
              _tmpStreamUrl = _cursor.getString(_cursorIndexOfStreamUrl);
            }
            final String _tmpLogoUrl;
            if (_cursor.isNull(_cursorIndexOfLogoUrl)) {
              _tmpLogoUrl = null;
            } else {
              _tmpLogoUrl = _cursor.getString(_cursorIndexOfLogoUrl);
            }
            final String _tmpCategory;
            if (_cursor.isNull(_cursorIndexOfCategory)) {
              _tmpCategory = null;
            } else {
              _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
            }
            final String _tmpCategoryId;
            if (_cursor.isNull(_cursorIndexOfCategoryId)) {
              _tmpCategoryId = null;
            } else {
              _tmpCategoryId = _cursor.getString(_cursorIndexOfCategoryId);
            }
            final String _tmpTvgId;
            if (_cursor.isNull(_cursorIndexOfTvgId)) {
              _tmpTvgId = null;
            } else {
              _tmpTvgId = _cursor.getString(_cursorIndexOfTvgId);
            }
            final String _tmpTvgName;
            if (_cursor.isNull(_cursorIndexOfTvgName)) {
              _tmpTvgName = null;
            } else {
              _tmpTvgName = _cursor.getString(_cursorIndexOfTvgName);
            }
            final String _tmpTvgLogo;
            if (_cursor.isNull(_cursorIndexOfTvgLogo)) {
              _tmpTvgLogo = null;
            } else {
              _tmpTvgLogo = _cursor.getString(_cursorIndexOfTvgLogo);
            }
            final String _tmpTvgChno;
            if (_cursor.isNull(_cursorIndexOfTvgChno)) {
              _tmpTvgChno = null;
            } else {
              _tmpTvgChno = _cursor.getString(_cursorIndexOfTvgChno);
            }
            final String _tmpEpgChannelId;
            if (_cursor.isNull(_cursorIndexOfEpgChannelId)) {
              _tmpEpgChannelId = null;
            } else {
              _tmpEpgChannelId = _cursor.getString(_cursorIndexOfEpgChannelId);
            }
            final boolean _tmpIsFavorite;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsFavorite);
            _tmpIsFavorite = _tmp != 0;
            final boolean _tmpIsLocked;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsLocked);
            _tmpIsLocked = _tmp_1 != 0;
            final int _tmpSortOrder;
            _tmpSortOrder = _cursor.getInt(_cursorIndexOfSortOrder);
            final long _tmpAddedAt;
            _tmpAddedAt = _cursor.getLong(_cursorIndexOfAddedAt);
            _item = new ChannelEntity(_tmpId,_tmpStreamId,_tmpName,_tmpStreamUrl,_tmpLogoUrl,_tmpCategory,_tmpCategoryId,_tmpTvgId,_tmpTvgName,_tmpTvgLogo,_tmpTvgChno,_tmpEpgChannelId,_tmpIsFavorite,_tmpIsLocked,_tmpSortOrder,_tmpAddedAt);
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
  public ChannelEntity getChannelById(final long id) {
    final String _sql = "SELECT * FROM channels WHERE id = ?";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 1);
    int _argIndex = 1;
    _statement.bindLong(_argIndex, id);
    __db.assertNotSuspendingTransaction();
    final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
    try {
      final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
      final int _cursorIndexOfStreamId = CursorUtil.getColumnIndexOrThrow(_cursor, "streamId");
      final int _cursorIndexOfName = CursorUtil.getColumnIndexOrThrow(_cursor, "name");
      final int _cursorIndexOfStreamUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "streamUrl");
      final int _cursorIndexOfLogoUrl = CursorUtil.getColumnIndexOrThrow(_cursor, "logoUrl");
      final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
      final int _cursorIndexOfCategoryId = CursorUtil.getColumnIndexOrThrow(_cursor, "categoryId");
      final int _cursorIndexOfTvgId = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgId");
      final int _cursorIndexOfTvgName = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgName");
      final int _cursorIndexOfTvgLogo = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgLogo");
      final int _cursorIndexOfTvgChno = CursorUtil.getColumnIndexOrThrow(_cursor, "tvgChno");
      final int _cursorIndexOfEpgChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "epgChannelId");
      final int _cursorIndexOfIsFavorite = CursorUtil.getColumnIndexOrThrow(_cursor, "isFavorite");
      final int _cursorIndexOfIsLocked = CursorUtil.getColumnIndexOrThrow(_cursor, "isLocked");
      final int _cursorIndexOfSortOrder = CursorUtil.getColumnIndexOrThrow(_cursor, "sortOrder");
      final int _cursorIndexOfAddedAt = CursorUtil.getColumnIndexOrThrow(_cursor, "addedAt");
      final ChannelEntity _result;
      if(_cursor.moveToFirst()) {
        final long _tmpId;
        _tmpId = _cursor.getLong(_cursorIndexOfId);
        final String _tmpStreamId;
        if (_cursor.isNull(_cursorIndexOfStreamId)) {
          _tmpStreamId = null;
        } else {
          _tmpStreamId = _cursor.getString(_cursorIndexOfStreamId);
        }
        final String _tmpName;
        if (_cursor.isNull(_cursorIndexOfName)) {
          _tmpName = null;
        } else {
          _tmpName = _cursor.getString(_cursorIndexOfName);
        }
        final String _tmpStreamUrl;
        if (_cursor.isNull(_cursorIndexOfStreamUrl)) {
          _tmpStreamUrl = null;
        } else {
          _tmpStreamUrl = _cursor.getString(_cursorIndexOfStreamUrl);
        }
        final String _tmpLogoUrl;
        if (_cursor.isNull(_cursorIndexOfLogoUrl)) {
          _tmpLogoUrl = null;
        } else {
          _tmpLogoUrl = _cursor.getString(_cursorIndexOfLogoUrl);
        }
        final String _tmpCategory;
        if (_cursor.isNull(_cursorIndexOfCategory)) {
          _tmpCategory = null;
        } else {
          _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
        }
        final String _tmpCategoryId;
        if (_cursor.isNull(_cursorIndexOfCategoryId)) {
          _tmpCategoryId = null;
        } else {
          _tmpCategoryId = _cursor.getString(_cursorIndexOfCategoryId);
        }
        final String _tmpTvgId;
        if (_cursor.isNull(_cursorIndexOfTvgId)) {
          _tmpTvgId = null;
        } else {
          _tmpTvgId = _cursor.getString(_cursorIndexOfTvgId);
        }
        final String _tmpTvgName;
        if (_cursor.isNull(_cursorIndexOfTvgName)) {
          _tmpTvgName = null;
        } else {
          _tmpTvgName = _cursor.getString(_cursorIndexOfTvgName);
        }
        final String _tmpTvgLogo;
        if (_cursor.isNull(_cursorIndexOfTvgLogo)) {
          _tmpTvgLogo = null;
        } else {
          _tmpTvgLogo = _cursor.getString(_cursorIndexOfTvgLogo);
        }
        final String _tmpTvgChno;
        if (_cursor.isNull(_cursorIndexOfTvgChno)) {
          _tmpTvgChno = null;
        } else {
          _tmpTvgChno = _cursor.getString(_cursorIndexOfTvgChno);
        }
        final String _tmpEpgChannelId;
        if (_cursor.isNull(_cursorIndexOfEpgChannelId)) {
          _tmpEpgChannelId = null;
        } else {
          _tmpEpgChannelId = _cursor.getString(_cursorIndexOfEpgChannelId);
        }
        final boolean _tmpIsFavorite;
        final int _tmp;
        _tmp = _cursor.getInt(_cursorIndexOfIsFavorite);
        _tmpIsFavorite = _tmp != 0;
        final boolean _tmpIsLocked;
        final int _tmp_1;
        _tmp_1 = _cursor.getInt(_cursorIndexOfIsLocked);
        _tmpIsLocked = _tmp_1 != 0;
        final int _tmpSortOrder;
        _tmpSortOrder = _cursor.getInt(_cursorIndexOfSortOrder);
        final long _tmpAddedAt;
        _tmpAddedAt = _cursor.getLong(_cursorIndexOfAddedAt);
        _result = new ChannelEntity(_tmpId,_tmpStreamId,_tmpName,_tmpStreamUrl,_tmpLogoUrl,_tmpCategory,_tmpCategoryId,_tmpTvgId,_tmpTvgName,_tmpTvgLogo,_tmpTvgChno,_tmpEpgChannelId,_tmpIsFavorite,_tmpIsLocked,_tmpSortOrder,_tmpAddedAt);
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
