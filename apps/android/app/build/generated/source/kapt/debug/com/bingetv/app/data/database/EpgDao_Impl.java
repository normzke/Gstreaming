package com.bingetv.app.data.database;

import android.database.Cursor;
import androidx.lifecycle.LiveData;
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
public final class EpgDao_Impl implements EpgDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<EpgProgramEntity> __insertionAdapterOfEpgProgramEntity;

  private final SharedSQLiteStatement __preparedStmtOfDeleteOldPrograms;

  private final SharedSQLiteStatement __preparedStmtOfDeleteAllPrograms;

  public EpgDao_Impl(RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfEpgProgramEntity = new EntityInsertionAdapter<EpgProgramEntity>(__db) {
      @Override
      public String createQuery() {
        return "INSERT OR REPLACE INTO `epg_programs` (`id`,`channelId`,`title`,`description`,`startTime`,`endTime`,`category`,`icon`,`rating`) VALUES (nullif(?, 0),?,?,?,?,?,?,?,?)";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, EpgProgramEntity value) {
        stmt.bindLong(1, value.getId());
        if (value.getChannelId() == null) {
          stmt.bindNull(2);
        } else {
          stmt.bindString(2, value.getChannelId());
        }
        if (value.getTitle() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getTitle());
        }
        if (value.getDescription() == null) {
          stmt.bindNull(4);
        } else {
          stmt.bindString(4, value.getDescription());
        }
        stmt.bindLong(5, value.getStartTime());
        stmt.bindLong(6, value.getEndTime());
        if (value.getCategory() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getCategory());
        }
        if (value.getIcon() == null) {
          stmt.bindNull(8);
        } else {
          stmt.bindString(8, value.getIcon());
        }
        if (value.getRating() == null) {
          stmt.bindNull(9);
        } else {
          stmt.bindString(9, value.getRating());
        }
      }
    };
    this.__preparedStmtOfDeleteOldPrograms = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "DELETE FROM epg_programs WHERE endTime < ?";
        return _query;
      }
    };
    this.__preparedStmtOfDeleteAllPrograms = new SharedSQLiteStatement(__db) {
      @Override
      public String createQuery() {
        final String _query = "DELETE FROM epg_programs";
        return _query;
      }
    };
  }

  @Override
  public void insertPrograms(final List<EpgProgramEntity> programs) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __insertionAdapterOfEpgProgramEntity.insert(programs);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void deleteOldPrograms(final long time) {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfDeleteOldPrograms.acquire();
    int _argIndex = 1;
    _stmt.bindLong(_argIndex, time);
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfDeleteOldPrograms.release(_stmt);
    }
  }

  @Override
  public void deleteAllPrograms() {
    __db.assertNotSuspendingTransaction();
    final SupportSQLiteStatement _stmt = __preparedStmtOfDeleteAllPrograms.acquire();
    __db.beginTransaction();
    try {
      _stmt.executeUpdateDelete();
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
      __preparedStmtOfDeleteAllPrograms.release(_stmt);
    }
  }

  @Override
  public LiveData<List<EpgProgramEntity>> getProgramsForChannel(final String channelId,
      final long currentTime) {
    final String _sql = "SELECT * FROM epg_programs WHERE channelId = ? AND endTime > ? ORDER BY startTime ASC";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 2);
    int _argIndex = 1;
    if (channelId == null) {
      _statement.bindNull(_argIndex);
    } else {
      _statement.bindString(_argIndex, channelId);
    }
    _argIndex = 2;
    _statement.bindLong(_argIndex, currentTime);
    return __db.getInvalidationTracker().createLiveData(new String[]{"epg_programs"}, false, new Callable<List<EpgProgramEntity>>() {
      @Override
      public List<EpgProgramEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "channelId");
          final int _cursorIndexOfTitle = CursorUtil.getColumnIndexOrThrow(_cursor, "title");
          final int _cursorIndexOfDescription = CursorUtil.getColumnIndexOrThrow(_cursor, "description");
          final int _cursorIndexOfStartTime = CursorUtil.getColumnIndexOrThrow(_cursor, "startTime");
          final int _cursorIndexOfEndTime = CursorUtil.getColumnIndexOrThrow(_cursor, "endTime");
          final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
          final int _cursorIndexOfIcon = CursorUtil.getColumnIndexOrThrow(_cursor, "icon");
          final int _cursorIndexOfRating = CursorUtil.getColumnIndexOrThrow(_cursor, "rating");
          final List<EpgProgramEntity> _result = new ArrayList<EpgProgramEntity>(_cursor.getCount());
          while(_cursor.moveToNext()) {
            final EpgProgramEntity _item;
            final long _tmpId;
            _tmpId = _cursor.getLong(_cursorIndexOfId);
            final String _tmpChannelId;
            if (_cursor.isNull(_cursorIndexOfChannelId)) {
              _tmpChannelId = null;
            } else {
              _tmpChannelId = _cursor.getString(_cursorIndexOfChannelId);
            }
            final String _tmpTitle;
            if (_cursor.isNull(_cursorIndexOfTitle)) {
              _tmpTitle = null;
            } else {
              _tmpTitle = _cursor.getString(_cursorIndexOfTitle);
            }
            final String _tmpDescription;
            if (_cursor.isNull(_cursorIndexOfDescription)) {
              _tmpDescription = null;
            } else {
              _tmpDescription = _cursor.getString(_cursorIndexOfDescription);
            }
            final long _tmpStartTime;
            _tmpStartTime = _cursor.getLong(_cursorIndexOfStartTime);
            final long _tmpEndTime;
            _tmpEndTime = _cursor.getLong(_cursorIndexOfEndTime);
            final String _tmpCategory;
            if (_cursor.isNull(_cursorIndexOfCategory)) {
              _tmpCategory = null;
            } else {
              _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
            }
            final String _tmpIcon;
            if (_cursor.isNull(_cursorIndexOfIcon)) {
              _tmpIcon = null;
            } else {
              _tmpIcon = _cursor.getString(_cursorIndexOfIcon);
            }
            final String _tmpRating;
            if (_cursor.isNull(_cursorIndexOfRating)) {
              _tmpRating = null;
            } else {
              _tmpRating = _cursor.getString(_cursorIndexOfRating);
            }
            _item = new EpgProgramEntity(_tmpId,_tmpChannelId,_tmpTitle,_tmpDescription,_tmpStartTime,_tmpEndTime,_tmpCategory,_tmpIcon,_tmpRating);
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
  public EpgProgramEntity getCurrentProgram(final String channelId, final long time) {
    final String _sql = "SELECT * FROM epg_programs WHERE channelId = ? AND startTime <= ? AND endTime > ? LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 3);
    int _argIndex = 1;
    if (channelId == null) {
      _statement.bindNull(_argIndex);
    } else {
      _statement.bindString(_argIndex, channelId);
    }
    _argIndex = 2;
    _statement.bindLong(_argIndex, time);
    _argIndex = 3;
    _statement.bindLong(_argIndex, time);
    __db.assertNotSuspendingTransaction();
    final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
    try {
      final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
      final int _cursorIndexOfChannelId = CursorUtil.getColumnIndexOrThrow(_cursor, "channelId");
      final int _cursorIndexOfTitle = CursorUtil.getColumnIndexOrThrow(_cursor, "title");
      final int _cursorIndexOfDescription = CursorUtil.getColumnIndexOrThrow(_cursor, "description");
      final int _cursorIndexOfStartTime = CursorUtil.getColumnIndexOrThrow(_cursor, "startTime");
      final int _cursorIndexOfEndTime = CursorUtil.getColumnIndexOrThrow(_cursor, "endTime");
      final int _cursorIndexOfCategory = CursorUtil.getColumnIndexOrThrow(_cursor, "category");
      final int _cursorIndexOfIcon = CursorUtil.getColumnIndexOrThrow(_cursor, "icon");
      final int _cursorIndexOfRating = CursorUtil.getColumnIndexOrThrow(_cursor, "rating");
      final EpgProgramEntity _result;
      if(_cursor.moveToFirst()) {
        final long _tmpId;
        _tmpId = _cursor.getLong(_cursorIndexOfId);
        final String _tmpChannelId;
        if (_cursor.isNull(_cursorIndexOfChannelId)) {
          _tmpChannelId = null;
        } else {
          _tmpChannelId = _cursor.getString(_cursorIndexOfChannelId);
        }
        final String _tmpTitle;
        if (_cursor.isNull(_cursorIndexOfTitle)) {
          _tmpTitle = null;
        } else {
          _tmpTitle = _cursor.getString(_cursorIndexOfTitle);
        }
        final String _tmpDescription;
        if (_cursor.isNull(_cursorIndexOfDescription)) {
          _tmpDescription = null;
        } else {
          _tmpDescription = _cursor.getString(_cursorIndexOfDescription);
        }
        final long _tmpStartTime;
        _tmpStartTime = _cursor.getLong(_cursorIndexOfStartTime);
        final long _tmpEndTime;
        _tmpEndTime = _cursor.getLong(_cursorIndexOfEndTime);
        final String _tmpCategory;
        if (_cursor.isNull(_cursorIndexOfCategory)) {
          _tmpCategory = null;
        } else {
          _tmpCategory = _cursor.getString(_cursorIndexOfCategory);
        }
        final String _tmpIcon;
        if (_cursor.isNull(_cursorIndexOfIcon)) {
          _tmpIcon = null;
        } else {
          _tmpIcon = _cursor.getString(_cursorIndexOfIcon);
        }
        final String _tmpRating;
        if (_cursor.isNull(_cursorIndexOfRating)) {
          _tmpRating = null;
        } else {
          _tmpRating = _cursor.getString(_cursorIndexOfRating);
        }
        _result = new EpgProgramEntity(_tmpId,_tmpChannelId,_tmpTitle,_tmpDescription,_tmpStartTime,_tmpEndTime,_tmpCategory,_tmpIcon,_tmpRating);
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
