package com.bingetv.app.data.database;

import android.database.Cursor;
import androidx.lifecycle.LiveData;
import androidx.room.EntityDeletionOrUpdateAdapter;
import androidx.room.EntityInsertionAdapter;
import androidx.room.RoomDatabase;
import androidx.room.RoomSQLiteQuery;
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import java.lang.Class;
import java.lang.Exception;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.Callable;
import javax.annotation.processing.Generated;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class UserPreferencesDao_Impl implements UserPreferencesDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<UserPreferencesEntity> __insertionAdapterOfUserPreferencesEntity;

  private final EntityDeletionOrUpdateAdapter<UserPreferencesEntity> __updateAdapterOfUserPreferencesEntity;

  public UserPreferencesDao_Impl(RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfUserPreferencesEntity = new EntityInsertionAdapter<UserPreferencesEntity>(__db) {
      @Override
      public String createQuery() {
        return "INSERT OR REPLACE INTO `user_preferences` (`id`,`gridColumns`,`logoSize`,`showChannelNumbers`,`showNowPlaying`,`parentalControlEnabled`,`parentalControlPin`,`defaultQuality`,`autoPlayNext`,`theme`) VALUES (?,?,?,?,?,?,?,?,?,?)";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, UserPreferencesEntity value) {
        stmt.bindLong(1, value.getId());
        stmt.bindLong(2, value.getGridColumns());
        if (value.getLogoSize() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getLogoSize());
        }
        final int _tmp = value.getShowChannelNumbers() ? 1 : 0;
        stmt.bindLong(4, _tmp);
        final int _tmp_1 = value.getShowNowPlaying() ? 1 : 0;
        stmt.bindLong(5, _tmp_1);
        final int _tmp_2 = value.getParentalControlEnabled() ? 1 : 0;
        stmt.bindLong(6, _tmp_2);
        if (value.getParentalControlPin() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getParentalControlPin());
        }
        if (value.getDefaultQuality() == null) {
          stmt.bindNull(8);
        } else {
          stmt.bindString(8, value.getDefaultQuality());
        }
        final int _tmp_3 = value.getAutoPlayNext() ? 1 : 0;
        stmt.bindLong(9, _tmp_3);
        if (value.getTheme() == null) {
          stmt.bindNull(10);
        } else {
          stmt.bindString(10, value.getTheme());
        }
      }
    };
    this.__updateAdapterOfUserPreferencesEntity = new EntityDeletionOrUpdateAdapter<UserPreferencesEntity>(__db) {
      @Override
      public String createQuery() {
        return "UPDATE OR ABORT `user_preferences` SET `id` = ?,`gridColumns` = ?,`logoSize` = ?,`showChannelNumbers` = ?,`showNowPlaying` = ?,`parentalControlEnabled` = ?,`parentalControlPin` = ?,`defaultQuality` = ?,`autoPlayNext` = ?,`theme` = ? WHERE `id` = ?";
      }

      @Override
      public void bind(SupportSQLiteStatement stmt, UserPreferencesEntity value) {
        stmt.bindLong(1, value.getId());
        stmt.bindLong(2, value.getGridColumns());
        if (value.getLogoSize() == null) {
          stmt.bindNull(3);
        } else {
          stmt.bindString(3, value.getLogoSize());
        }
        final int _tmp = value.getShowChannelNumbers() ? 1 : 0;
        stmt.bindLong(4, _tmp);
        final int _tmp_1 = value.getShowNowPlaying() ? 1 : 0;
        stmt.bindLong(5, _tmp_1);
        final int _tmp_2 = value.getParentalControlEnabled() ? 1 : 0;
        stmt.bindLong(6, _tmp_2);
        if (value.getParentalControlPin() == null) {
          stmt.bindNull(7);
        } else {
          stmt.bindString(7, value.getParentalControlPin());
        }
        if (value.getDefaultQuality() == null) {
          stmt.bindNull(8);
        } else {
          stmt.bindString(8, value.getDefaultQuality());
        }
        final int _tmp_3 = value.getAutoPlayNext() ? 1 : 0;
        stmt.bindLong(9, _tmp_3);
        if (value.getTheme() == null) {
          stmt.bindNull(10);
        } else {
          stmt.bindString(10, value.getTheme());
        }
        stmt.bindLong(11, value.getId());
      }
    };
  }

  @Override
  public void insertPreferences(final UserPreferencesEntity preferences) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __insertionAdapterOfUserPreferencesEntity.insert(preferences);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public void updatePreferences(final UserPreferencesEntity preferences) {
    __db.assertNotSuspendingTransaction();
    __db.beginTransaction();
    try {
      __updateAdapterOfUserPreferencesEntity.handle(preferences);
      __db.setTransactionSuccessful();
    } finally {
      __db.endTransaction();
    }
  }

  @Override
  public LiveData<UserPreferencesEntity> getPreferences() {
    final String _sql = "SELECT * FROM user_preferences WHERE id = 1 LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    return __db.getInvalidationTracker().createLiveData(new String[]{"user_preferences"}, false, new Callable<UserPreferencesEntity>() {
      @Override
      public UserPreferencesEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfGridColumns = CursorUtil.getColumnIndexOrThrow(_cursor, "gridColumns");
          final int _cursorIndexOfLogoSize = CursorUtil.getColumnIndexOrThrow(_cursor, "logoSize");
          final int _cursorIndexOfShowChannelNumbers = CursorUtil.getColumnIndexOrThrow(_cursor, "showChannelNumbers");
          final int _cursorIndexOfShowNowPlaying = CursorUtil.getColumnIndexOrThrow(_cursor, "showNowPlaying");
          final int _cursorIndexOfParentalControlEnabled = CursorUtil.getColumnIndexOrThrow(_cursor, "parentalControlEnabled");
          final int _cursorIndexOfParentalControlPin = CursorUtil.getColumnIndexOrThrow(_cursor, "parentalControlPin");
          final int _cursorIndexOfDefaultQuality = CursorUtil.getColumnIndexOrThrow(_cursor, "defaultQuality");
          final int _cursorIndexOfAutoPlayNext = CursorUtil.getColumnIndexOrThrow(_cursor, "autoPlayNext");
          final int _cursorIndexOfTheme = CursorUtil.getColumnIndexOrThrow(_cursor, "theme");
          final UserPreferencesEntity _result;
          if(_cursor.moveToFirst()) {
            _result = new UserPreferencesEntity();
            final int _tmpId;
            _tmpId = _cursor.getInt(_cursorIndexOfId);
            _result.setId(_tmpId);
            final int _tmpGridColumns;
            _tmpGridColumns = _cursor.getInt(_cursorIndexOfGridColumns);
            _result.setGridColumns(_tmpGridColumns);
            final String _tmpLogoSize;
            if (_cursor.isNull(_cursorIndexOfLogoSize)) {
              _tmpLogoSize = null;
            } else {
              _tmpLogoSize = _cursor.getString(_cursorIndexOfLogoSize);
            }
            _result.setLogoSize(_tmpLogoSize);
            final boolean _tmpShowChannelNumbers;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfShowChannelNumbers);
            _tmpShowChannelNumbers = _tmp != 0;
            _result.setShowChannelNumbers(_tmpShowChannelNumbers);
            final boolean _tmpShowNowPlaying;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfShowNowPlaying);
            _tmpShowNowPlaying = _tmp_1 != 0;
            _result.setShowNowPlaying(_tmpShowNowPlaying);
            final boolean _tmpParentalControlEnabled;
            final int _tmp_2;
            _tmp_2 = _cursor.getInt(_cursorIndexOfParentalControlEnabled);
            _tmpParentalControlEnabled = _tmp_2 != 0;
            _result.setParentalControlEnabled(_tmpParentalControlEnabled);
            final String _tmpParentalControlPin;
            if (_cursor.isNull(_cursorIndexOfParentalControlPin)) {
              _tmpParentalControlPin = null;
            } else {
              _tmpParentalControlPin = _cursor.getString(_cursorIndexOfParentalControlPin);
            }
            _result.setParentalControlPin(_tmpParentalControlPin);
            final String _tmpDefaultQuality;
            if (_cursor.isNull(_cursorIndexOfDefaultQuality)) {
              _tmpDefaultQuality = null;
            } else {
              _tmpDefaultQuality = _cursor.getString(_cursorIndexOfDefaultQuality);
            }
            _result.setDefaultQuality(_tmpDefaultQuality);
            final boolean _tmpAutoPlayNext;
            final int _tmp_3;
            _tmp_3 = _cursor.getInt(_cursorIndexOfAutoPlayNext);
            _tmpAutoPlayNext = _tmp_3 != 0;
            _result.setAutoPlayNext(_tmpAutoPlayNext);
            final String _tmpTheme;
            if (_cursor.isNull(_cursorIndexOfTheme)) {
              _tmpTheme = null;
            } else {
              _tmpTheme = _cursor.getString(_cursorIndexOfTheme);
            }
            _result.setTheme(_tmpTheme);
          } else {
            _result = null;
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
  public UserPreferencesEntity getPreferencesSync() {
    final String _sql = "SELECT * FROM user_preferences WHERE id = 1 LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    __db.assertNotSuspendingTransaction();
    final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
    try {
      final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
      final int _cursorIndexOfGridColumns = CursorUtil.getColumnIndexOrThrow(_cursor, "gridColumns");
      final int _cursorIndexOfLogoSize = CursorUtil.getColumnIndexOrThrow(_cursor, "logoSize");
      final int _cursorIndexOfShowChannelNumbers = CursorUtil.getColumnIndexOrThrow(_cursor, "showChannelNumbers");
      final int _cursorIndexOfShowNowPlaying = CursorUtil.getColumnIndexOrThrow(_cursor, "showNowPlaying");
      final int _cursorIndexOfParentalControlEnabled = CursorUtil.getColumnIndexOrThrow(_cursor, "parentalControlEnabled");
      final int _cursorIndexOfParentalControlPin = CursorUtil.getColumnIndexOrThrow(_cursor, "parentalControlPin");
      final int _cursorIndexOfDefaultQuality = CursorUtil.getColumnIndexOrThrow(_cursor, "defaultQuality");
      final int _cursorIndexOfAutoPlayNext = CursorUtil.getColumnIndexOrThrow(_cursor, "autoPlayNext");
      final int _cursorIndexOfTheme = CursorUtil.getColumnIndexOrThrow(_cursor, "theme");
      final UserPreferencesEntity _result;
      if(_cursor.moveToFirst()) {
        _result = new UserPreferencesEntity();
        final int _tmpId;
        _tmpId = _cursor.getInt(_cursorIndexOfId);
        _result.setId(_tmpId);
        final int _tmpGridColumns;
        _tmpGridColumns = _cursor.getInt(_cursorIndexOfGridColumns);
        _result.setGridColumns(_tmpGridColumns);
        final String _tmpLogoSize;
        if (_cursor.isNull(_cursorIndexOfLogoSize)) {
          _tmpLogoSize = null;
        } else {
          _tmpLogoSize = _cursor.getString(_cursorIndexOfLogoSize);
        }
        _result.setLogoSize(_tmpLogoSize);
        final boolean _tmpShowChannelNumbers;
        final int _tmp;
        _tmp = _cursor.getInt(_cursorIndexOfShowChannelNumbers);
        _tmpShowChannelNumbers = _tmp != 0;
        _result.setShowChannelNumbers(_tmpShowChannelNumbers);
        final boolean _tmpShowNowPlaying;
        final int _tmp_1;
        _tmp_1 = _cursor.getInt(_cursorIndexOfShowNowPlaying);
        _tmpShowNowPlaying = _tmp_1 != 0;
        _result.setShowNowPlaying(_tmpShowNowPlaying);
        final boolean _tmpParentalControlEnabled;
        final int _tmp_2;
        _tmp_2 = _cursor.getInt(_cursorIndexOfParentalControlEnabled);
        _tmpParentalControlEnabled = _tmp_2 != 0;
        _result.setParentalControlEnabled(_tmpParentalControlEnabled);
        final String _tmpParentalControlPin;
        if (_cursor.isNull(_cursorIndexOfParentalControlPin)) {
          _tmpParentalControlPin = null;
        } else {
          _tmpParentalControlPin = _cursor.getString(_cursorIndexOfParentalControlPin);
        }
        _result.setParentalControlPin(_tmpParentalControlPin);
        final String _tmpDefaultQuality;
        if (_cursor.isNull(_cursorIndexOfDefaultQuality)) {
          _tmpDefaultQuality = null;
        } else {
          _tmpDefaultQuality = _cursor.getString(_cursorIndexOfDefaultQuality);
        }
        _result.setDefaultQuality(_tmpDefaultQuality);
        final boolean _tmpAutoPlayNext;
        final int _tmp_3;
        _tmp_3 = _cursor.getInt(_cursorIndexOfAutoPlayNext);
        _tmpAutoPlayNext = _tmp_3 != 0;
        _result.setAutoPlayNext(_tmpAutoPlayNext);
        final String _tmpTheme;
        if (_cursor.isNull(_cursorIndexOfTheme)) {
          _tmpTheme = null;
        } else {
          _tmpTheme = _cursor.getString(_cursorIndexOfTheme);
        }
        _result.setTheme(_tmpTheme);
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
